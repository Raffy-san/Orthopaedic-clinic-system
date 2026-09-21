/**
 * build-boundaries.js
 *
 * Fetches Philippine administrative boundary GeoJSON from
 * https://github.com/faeldon/philippines-json-maps (2023 dataset, lowres)
 * for one or more named provinces, and writes ONE JSON FILE PER PROVINCE
 * into ./boundaries/<province-slug>.json
 *
 * Confirmed real file structure (verified against the live repo on 2026-09-20):
 *
 *   Provinces (level 2), grouped per REGION:
 *     2023/geojson/regions/lowres/provdists-region-<regionPsgc>.0.001.json
 *     properties: { adm1_psgc, adm2_psgc, adm2_en, geo_level }
 *
 *   Cities/Municipalities (level 3), grouped per PROVINCE:
 *     2023/geojson/provdists/lowres/municities-provdist-<provincePsgc>.0.001.json
 *     properties: { adm1_psgc, adm2_psgc, adm3_psgc, adm3_en, geo_level }
 *
 *   Barangays (level 4), grouped per CITY/MUNICIPALITY:
 *     2023/geojson/municities/lowres/bgysubmuns-municity-<cityPsgc>.0.001.json
 *     properties: { adm1_psgc, adm2_psgc, adm3_psgc, adm4_psgc, adm4_en, geo_level }
 *
 * IMPORTANT: PSGC codes in these filenames/properties are plain integers
 * with leading zeros DROPPED (e.g. Region VIII is "800000000", not
 * "0800000000"; Southern Leyte province is "806400000", not "0806400000").
 *
 * Since barangay files are split per city/municipality, building one
 * province's data means: 1 fetch for the province's own shape, 1 fetch for
 * its list of cities/municipalities, then 1 fetch PER city/municipality for
 * that city's barangays. For Southern Leyte (19 cities/municipalities)
 * that's ~21 total requests for that one province.
 *
 * Usage:
 *   node build-boundaries.js
 *
 * Requires Node 18+ (for built-in fetch).
 */

const fs = require('fs');
const path = require('path');

const BASE = 'https://raw.githubusercontent.com/faeldon/philippines-json-maps/master/2023/geojson';
const OUT_DIR = path.join(__dirname, 'boundaries');

// ---- Add provinces here as you need them ----
// regionPsgc:   the province's REGION code (unpadded, no leading zero)
// provincePsgc: the province's own code (unpadded, no leading zero)
// name:         used to match the right feature by name, and to build the output filename
const PROVINCES = [
  { regionPsgc: 800000000, provincePsgc: 806400000, name: 'Southern Leyte' },
  // Example for a neighboring province — fill in the real codes before enabling:
  // { regionPsgc: 800000000, provincePsgc: 806100000, name: 'Leyte' },
];

async function getJSON(url) {
  console.log(`Fetching ${url} ...`);
  const res = await fetch(url);
  if (!res.ok) {
    throw new Error(`Failed to fetch ${url}: ${res.status} ${res.statusText}`);
  }
  return res.json();
}

// Simple centroid: average of all coordinate points in the geometry.
// Good enough for marker placement; not a true polygon-area centroid.
function computeCentroid(geometry) {
  if (!geometry) return null;
  let coords = [];

  function collect(rings) {
    for (const ring of rings) {
      if (Array.isArray(ring[0])) {
        collect(ring);
      } else {
        coords.push(ring);
      }
    }
  }

  if (geometry.type === 'Polygon') {
    collect(geometry.coordinates);
  } else if (geometry.type === 'MultiPolygon') {
    for (const polygon of geometry.coordinates) {
      collect(polygon);
    }
  } else {
    return null;
  }

  if (coords.length === 0) return null;

  const sum = coords.reduce((acc, [lng, lat]) => [acc[0] + lng, acc[1] + lat], [0, 0]);
  return {
    lat: sum[1] / coords.length,
    lng: sum[0] / coords.length
  };
}

function slugify(name) {
  return name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
}

async function buildProvince({ regionPsgc, provincePsgc, name }) {
  console.log(`\n=== Building ${name} (province ${provincePsgc}, region ${regionPsgc}) ===`);

  // 1. Province-level shape comes from its region's file
  const regionFC = await getJSON(`${BASE}/regions/lowres/provdists-region-${regionPsgc}.0.001.json`);
  const provinceFeature = regionFC.features.find((f) => f.properties.adm2_psgc === provincePsgc);
  if (!provinceFeature) {
    throw new Error(`Province ${name} (${provincePsgc}) not found in region ${regionPsgc} file.`);
  }

  const province = {
    code: provinceFeature.properties.adm2_psgc,
    name: provinceFeature.properties.adm2_en,
    centroid: computeCentroid(provinceFeature.geometry),
    boundary: provinceFeature.geometry
  };

  // 2. Cities/municipalities within this province
  const provinceFC = await getJSON(`${BASE}/provdists/lowres/municities-provdist-${provincePsgc}.0.001.json`);
  const cityFeatures = provinceFC.features;

  const cities = cityFeatures.map((f) => ({
    code: f.properties.adm3_psgc,
    name: f.properties.adm3_en,
    centroid: computeCentroid(f.geometry),
    boundary: f.geometry
  }));

  console.log(`Found ${cities.length} cities/municipalities in ${name}.`);

  // 3. Barangays — one fetch PER city/municipality
  const barangays = [];
  for (const city of cities) {
    try {
      const cityFC = await getJSON(`${BASE}/municities/lowres/bgysubmuns-municity-${city.code}.0.001.json`);
      for (const f of cityFC.features) {
        barangays.push({
          code: f.properties.adm4_psgc,
          name: f.properties.adm4_en,
          cityCode: city.code,
          centroid: computeCentroid(f.geometry),
          boundary: f.geometry
        });
      }
      console.log(`  ${city.name}: ${cityFC.features.length} barangays`);
    } catch (err) {
      console.warn(`  Skipping ${city.name} (${city.code}): ${err.message}`);
    }
  }

  return { province, cities, barangays };
}

async function main() {
  fs.mkdirSync(OUT_DIR, { recursive: true });

  for (const entry of PROVINCES) {
    const result = await buildProvince(entry);
    const outPath = path.join(OUT_DIR, `${slugify(entry.name)}.json`);
    fs.writeFileSync(outPath, JSON.stringify(result));
    console.log(
      `Wrote ${outPath} (${result.cities.length} cities, ${result.barangays.length} barangays, ` +
      `${(fs.statSync(outPath).size / 1024 / 1024).toFixed(2)} MB)`
    );
  }

  console.log('\nDone.');
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});