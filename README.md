# BASIC Line Renumbering Tool  
> by E. Wenners, 2026

This tool renumbers classic line‑based BASIC programs — including Commodore 64 BASIC and many other dialects that use `GOTO`, `GOSUB`, and numeric line references.

It takes your BASIC listing, renumbers every line, and automatically updates all jump targets so the program remains functional after cleanup.

## Usage
- Paste your source code into the textarea  
- Set the starting line number (default: 10)  
- Set the increment (default: 10)  
- Choose whether to insert a space between the line number and the code (default: on)  
- Click **Renumber**

## What the tool updates
The renumbering engine automatically adjusts:

- `GOTO <line>`
- `GOSUB <line>`
- `THEN <line>`
- `ON X GOTO <line1>,<line2>,<line3>...`
- `ON X GOSUB <line1>,<line2>,<line3>...`
- All jump targets outside of quoted strings

It also:

- Replaces empty lines with `REM *` to preserve numbering  
- Leaves all text inside strings untouched  

## Notes
This tool is written in PHP and works entirely server‑side — no dependencies, no installation, just paste and renumber.

## Disclaimer
This tool is provided as‑is without any warranties. Always keep a backup of your original code before using the renumbering tool.

## Try it online
Test it here: https://chaozz.nl/github/renumber.php
