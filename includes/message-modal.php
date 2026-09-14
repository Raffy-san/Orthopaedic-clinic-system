<div id="messageModal"
    class="modal hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-[9999] px-2 sm:px-0"
    style="background-color: rgba(0,0,0,0.4);">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-md w-full text-center">
        <h3 id="messageTitle" class="text-base sm:text-lg font-semibold mb-2"></h3>
        <p id="messageText" class="text-gray-600 mb-4 text-sm sm:text-base"></p>
        <button id="closeMessageBtn"
            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 text-sm sm:text-base cursor-pointer">OK</button>
    </div>
</div>

<div id="confirmModal"
    class="modal hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-[9999] px-2 sm:px-0"
    style="background-color: rgba(0,0,0,0.4);">
    <div class="bg-white rounded-lg p-4 sm:p-6 max-w-md w-full text-center">
        <h3 id="confirmTitle" class="text-base sm:text-lg font-semibold mb-2"></h3>
        <p id="confirmText" class="text-gray-600 mb-4 text-sm sm:text-base"></p>
        <div class="flex justify-center gap-3">
            <button id="confirmCancelBtn"
                class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 text-sm sm:text-base cursor-pointer">Cancel</button>
            <button id="confirmOkBtn"
                class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600 text-sm sm:text-base cursor-pointer">Confirm</button>
        </div>
    </div>
</div>