/**
 * build-psgc-json.js
 *
 * Fetches PSGC province, city/municipality, and barangay data from the
 * public PSGC API (https://psgc.gitlab.io/api/) and shapes it into a
 * single nested JSON file:
 *
 *   [
 *     {
 *       code: "...",
 *       name: "...",
 *       cities: [
 *         {
 *           code: "...",
 *           name: "...",
 *           barangays: [ { code: "...", name: "..." }, ... ]
 *         },
 *         ...
 *       ]
 *     },
 *     ...
 *   ]
 *
 * Usage:
 *   node build-psgc-json.js
 *
 * Requires Node 18+ (for built-in fetch). Produces ./psgc.json
 */

const fs = require('fs');

const BASE = 'https://psgc.gitlab.io/api';

async function getJSON(url) {
  console.log(`Fetching ${url} ...`);
  const res = await fetch(url);
  if (!res.ok) {
    throw new Error(`Failed to fetch ${url}: ${res.status} ${res.statusText}`);
  }
  return res.json();
}

async function main() {
  // 1. Pull the three flat datasets once.
  const [provinces, cities, barangays] = await Promise.all([
    getJSON(`${BASE}/provinces/`),
    getJSON(`${BASE}/cities-municipalities/`),
    getJSON(`${BASE}/barangays/`),
  ]);

  console.log(
    `Fetched ${provinces.length} provinces, ${cities.length} cities/municipalities, ${barangays.length} barangays.`
  );

  // 2. Group barangays by their parent city/municipality code for fast lookup.
  const barangaysByCity = new Map();
  for (const b of barangays) {
    const parentCode = b.municipalityCode || b.cityCode;
    if (!parentCode) continue;
    if (!barangaysByCity.has(parentCode)) barangaysByCity.set(parentCode, []);
    barangaysByCity.get(parentCode).push({ code: b.code, name: b.name });
  }

  // 3. Group cities/municipalities by their parent province code.
  const citiesByProvince = new Map();
  for (const c of cities) {
    const parentCode = c.provinceCode;
    if (!parentCode) continue;
    if (!citiesByProvince.has(parentCode)) citiesByProvince.set(parentCode, []);
    citiesByProvince.get(parentCode).push({
      code: c.code,
      name: c.name,
      barangays: (barangaysByCity.get(c.code) || []).sort((a, b) =>
        a.name.localeCompare(b.name)
      ),
    });
  }

  // 4. Build the final nested tree, sorted alphabetically at every level.
  const tree = provinces
    .map((p) => ({
      code: p.code,
      name: p.name,
      cities: (citiesByProvince.get(p.code) || []).sort((a, b) =>
        a.name.localeCompare(b.name)
      ),
    }))
    .sort((a, b) => a.name.localeCompare(b.name));

  fs.writeFileSync('psgc.json', JSON.stringify(tree));
  console.log(`Done. Wrote psgc.json (${(fs.statSync('psgc.json').size / 1024 / 1024).toFixed(2)} MB).`);
}

main().catch((err) => {
  console.error(err);
  process.exit(1);
});
