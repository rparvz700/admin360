const XLSX = require('./node_modules/xlsx/xlsx.js');
const path = require('path');

const filePath = path.join(__dirname, 'public', 'media', '360', 'finance', 'outflow.xlsx');

try {
    const workbook = XLSX.readFile(filePath);
    console.log('Sheet Names:', workbook.SheetNames);
    console.log('Total Sheets:', workbook.SheetNames.length);
    console.log('');

    workbook.SheetNames.forEach(sheetName => {
        const sheet = workbook.Sheets[sheetName];
        console.log('================================================================');
        console.log(`SHEET: "${sheetName}"`);
        console.log('Range:', sheet['!ref']);
        console.log('================================================================');
        
        // Get all data as JSON
        const data = XLSX.utils.sheet_to_json(sheet, { header: 1, defval: null, raw: true });
        data.forEach((row, idx) => {
            const hasData = row.some(cell => cell !== null && cell !== undefined && cell !== '');
            if (hasData) {
                console.log(`Row ${idx + 1}:`, JSON.stringify(row));
            }
        });
        
        // Show formulas
        console.log('\n--- FORMULAS ---');
        let formulaCount = 0;
        Object.keys(sheet).forEach(cellRef => {
            if (cellRef.startsWith('!')) return;
            const cell = sheet[cellRef];
            if (cell.f) {
                formulaCount++;
                console.log(`  ${cellRef}: =${cell.f} => ${cell.v}`);
            }
        });
        if (formulaCount === 0) console.log('  (No formulas found)');
        
        // Show merge cells
        if (sheet['!merges'] && sheet['!merges'].length > 0) {
            console.log('\n--- MERGED CELLS ---');
            sheet['!merges'].forEach(merge => {
                console.log(`  ${XLSX.utils.encode_range(merge)}`);
            });
        }
        console.log('\n');
    });
} catch (e) {
    console.error('Error:', e.message);
    
    // Try alternative path
    try {
        const fs = require('fs');
        console.log('File exists:', fs.existsSync(filePath));
        console.log('File path:', filePath);
    } catch (e2) {
        console.error('FS Error:', e2.message);
    }
}
