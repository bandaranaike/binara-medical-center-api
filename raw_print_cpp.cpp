#include <windows.h>
#include <iostream> // For std::cout, std::cerr
#include <vector>   // For std::vector
#include <string>   // For std::string
#include <cstring>  // For strlen, memcpy (still useful for byte operations)
// #include <stdexcept> // Optional: for std::runtime_error if you prefer C++ exceptions

// --- Configuration ---
// IMPORTANT: Change this to the exact name of your printer in Windows
const char* G_PRINTER_NAME_CPP = "EPSON LQ-310";

// Function to send raw data to the printer (C++ style)
bool sendRawDataToPrinterCpp(const char* szPrinterName, const std::vector<BYTE>& dataToSend) {
    HANDLE hPrinter = NULL;
    DOC_INFO_1A DocInfo; // Using ANSI version for char* compatibility
    DWORD dwJob = 0;
    DWORD dwBytesWritten = 0;
    bool success = false;

    // 1. Open a handle to the printer.
    // OpenPrinterA expects LPSTR (char*), so if szPrinterName is const char*, a const_cast is common.
    // It's generally safe as OpenPrinterA doesn't modify the name string.
    if (!OpenPrinterA(const_cast<LPSTR>(szPrinterName), &hPrinter, NULL)) {
        std::cerr << "Error: Failed to open printer '" << szPrinterName << "'. Error code: " << GetLastError() << std::endl;
        return false;
    }

    // The following block ensures resources are cleaned up.
    // A more advanced C++ approach would use RAII classes for hPrinter, dwJob, etc.
    // to automate cleanup, especially in the presence of exceptions or multiple return paths.

    // 2. Fill in the DOC_INFO_1 structure.
    memset(&DocInfo, 0, sizeof(DocInfo));
    DocInfo.pDocName = (LPSTR)"Cpp Raw Print Job"; // Casting string literal to LPSTR
    DocInfo.pOutputFile = NULL;
    DocInfo.pDatatype = (LPSTR)"RAW";             // Specify RAW data type

    // 3. Notify the spooler that a document is beginning.
    dwJob = StartDocPrinterA(hPrinter, 1, reinterpret_cast<LPBYTE>(&DocInfo));
    if (dwJob == 0) {
        std::cerr << "Error: Failed to start document. Error code: " << GetLastError() << std::endl;
        ClosePrinter(hPrinter); // Clean up printer handle
        return false;
    }

    // 4. Start a page.
    if (!StartPagePrinter(hPrinter)) {
        std::cerr << "Error: Failed to start page. Error code: " << GetLastError() << std::endl;
        EndDocPrinter(hPrinter);   // Clean up document
        ClosePrinter(hPrinter);    // Clean up printer handle
        return false;
    }

    // 5. Send the data to the printer.
    if (!WritePrinter(hPrinter, (LPVOID)dataToSend.data(), static_cast<DWORD>(dataToSend.size()), &dwBytesWritten)) {
        std::cerr << "Error: WritePrinter failed. Error code: " << GetLastError() << std::endl;
        // Continue to cleanup, but job is likely failed
    } else if (dwBytesWritten != dataToSend.size()) {
        std::cerr << "Warning: Not all bytes were written. Sent: " << dataToSend.size()
                  << ", Written: " << dwBytesWritten << std::endl;
    } else {
        std::cout << "Data successfully sent to printer (" << dwBytesWritten << " bytes)." << std::endl;
        success = true; // Mark as successful if WritePrinter sent all bytes
    }

    // 6. End the page.
    if (!EndPagePrinter(hPrinter)) {
        std::cerr << "Error: Failed to end page. Error code: " << GetLastError() << std::endl;
        success = false; // If EndPagePrinter fails, the print job might be corrupted or incomplete.
    }

    // 7. End the document.
    if (!EndDocPrinter(hPrinter)) {
        std::cerr << "Error: Failed to end document. Error code: " << GetLastError() << std::endl;
        success = false;
    }

    // 8. Close the printer handle.
    if (!ClosePrinter(hPrinter)) {
        std::cerr << "Error: Failed to close printer. Error code: " << GetLastError() << std::endl;
        success = false;
    }

    return success;
}

int main() {
    // --- Define ESC/P Commands (as const BYTE arrays) ---
    const BYTE CMD_INIT_PRINTER[]         = { 0x1B, '@' };                      // ESC @ (Initialize)
    const BYTE CMD_SET_LINE_SPACING_1_6[] = { 0x1B, '2' };                      // ESC 2 (Set line spacing to 1/6 inch)

    // For 5-inch paper at 6 Lines Per Inch (LPI) = 30 lines
    const BYTE PAGE_LENGTH_LINES          = 30;
    const BYTE CMD_SET_PAGE_LENGTH[]      = { 0x1B, 'C', PAGE_LENGTH_LINES };  // ESC C n (Set page length to n lines)

    const BYTE CMD_FORM_FEED[]            = { 0x0C };                           // Form Feed (eject page)

    // --- Prepare Text Data (using std::string) ---
    std::string textBillData =
        "  Binara Medical Centre (C++ Example)\n"
        "  -----------------------------------\n"
        "  Bill No.: CPP-002   Date: 31/05/2025\n"
        "  Customer: Another C++ User\n"
        "\n"
        "  Services:\n"
        "    Service Epsilon - Rs. 1800.00\n"
        "    Service Zeta    - Rs.  700.00\n"
        "\n"
        "  Total:            Rs. 2500.00\n"
        "\n\n\n"; // Extra newlines for spacing before FF

    // --- Combine commands and data into a std::vector<BYTE> ---
    std::vector<BYTE> printBuffer;

    // Helper lambda to append raw byte arrays to the vector
    auto append_bytes_to_buffer = [&](const BYTE arr[], size_t size) {
        printBuffer.insert(printBuffer.end(), arr, arr + size);
    };

    append_bytes_to_buffer(CMD_INIT_PRINTER, sizeof(CMD_INIT_PRINTER));
    append_bytes_to_buffer(CMD_SET_LINE_SPACING_1_6, sizeof(CMD_SET_LINE_SPACING_1_6));
    append_bytes_to_buffer(CMD_SET_PAGE_LENGTH, sizeof(CMD_SET_PAGE_LENGTH));

    // Append text data (from std::string)
    // textBillData.c_str() gives a const char*, which is compatible with const BYTE* for ASCII/UTF-8 text
    printBuffer.insert(printBuffer.end(),
                       reinterpret_cast<const BYTE*>(textBillData.c_str()),
                       reinterpret_cast<const BYTE*>(textBillData.c_str() + textBillData.length()));

    append_bytes_to_buffer(CMD_FORM_FEED, sizeof(CMD_FORM_FEED));

    // --- Send the combined data to the printer ---
    if (sendRawDataToPrinterCpp(G_PRINTER_NAME_CPP, printBuffer)) {
        std::cout << "Print job submitted successfully via C++." << std::endl;
    } else {
        std::cout << "Print job failed via C++." << std::endl;
    }

    return 0;
}
