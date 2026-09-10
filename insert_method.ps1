# Read the clean file
$content = [System.IO.File]::ReadAllText("C:\laragon\www\plms_backup\clean_controller.php")

# Define the method to insert
$newMethod = @"

public function landlordTaxInvoiceReportDownload(string $token)
{
    $info = cache()->get('ltir_dl_' . $token);
    abort_if(!$info || !file_exists($info['path']), 404, 'File not found or expired.');
    return response()->download($info['path'], $info['name'])->deleteFileAfterSend(true);
}
"@

# Find the line "}\n\n/**" that marks the end of landlordTaxInvoiceReportStream method
# We need to insert after "}\n" (which is at line 4077-4078)
$searchPattern = "}\r\n\r\n/**`r`n * Landlord contracts for the given vendor"
$replacement = "}`r`n" + $newMethod + "`r`n`r`n/**`r`n * Landlord contracts for the given vendor"

$newContent = $content -replace $searchPattern, $replacement

# Write the modified content back
[System.IO.File]::WriteAllText("C:\laragon\www\plms_backup\Modules\BackOffice\Http\Controllers\BackOfficeReportController.php", $newContent)
