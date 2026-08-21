const fs = require('fs');

const raw = fs.readFileSync('pincodes.json', 'utf8');
const data = JSON.parse(raw);
let sql = `CREATE TABLE IF NOT EXISTS pincodes (pincode TEXT PRIMARY KEY, city TEXT, state TEXT);\nBEGIN TRANSACTION;\n`;

let count = 0;
const uniquePins = new Set();

for (let row of data) {
    if (!row.pincode || !row.districtName || !row.stateName) continue;
    
    let pin = row.pincode.toString();
    if (uniquePins.has(pin)) continue;
    uniquePins.add(pin);
    
    let city = row.districtName.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()).replace(/'/g, "''");
    let state = row.stateName.toLowerCase().replace(/\b\w/g, c => c.toUpperCase()).replace(/'/g, "''");
    
    sql += `INSERT OR REPLACE INTO pincodes (pincode, city, state) VALUES ('${pin}', '${city}', '${state}');\n`;
    count++;
}

sql += `COMMIT;\n`;
fs.writeFileSync('pincodes.sql', sql);
console.log(`Generated SQL for ${count} unique pincodes.`);
