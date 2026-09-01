# Report source

The final report is generated from these scripts so it can be rebuilt after a
change to the system, rather than edited by hand and left to drift.

`schema.txt` is a dump of the live database columns; the table designs in
section 3.5.2 are generated from it, so they cannot disagree with the real
schema.

## Rebuilding

This needs Node only for the document generator. It is **not** a dependency of
the application, which still has no build step.

```bash
npm install docx
node assemble.js
```

The output is written to `../EAMU-Attendance-System-Final-Report.docx`.

To refresh `schema.txt` after a migration change:

```bash
mysql -u root --batch --skip-column-names -e "SELECT CONCAT(TABLE_NAME,'|',COLUMN_NAME,'|',COLUMN_TYPE,'|',IS_NULLABLE,'|',IFNULL(COLUMN_KEY,''),'|',IFNULL(COLUMN_DEFAULT,'')) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA='individualAssignment' ORDER BY TABLE_NAME, ORDINAL_POSITION;" > schema.txt
```
