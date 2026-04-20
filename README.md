# Commodore 64 BASIC Line Renumbering Tool
> by E. Wenners, 2026

With this tool you can renumber a Commodore 64 BASIC project (*and probably other flavors of BASIC*).

Usage:
* Paste your sourcecode into the textarea
* Set the starting line number (the first line number)
* Set the increment
* Pick if you want a space between the line number and the code
* Hit **Renumber**

The tool will not only renumber the lines, but also fix all:
* GOTO <line>
* GOSUB <line>
* THEN <line>
* ON X GOTO <line-1>,<line-2>,<line-3>

**Note:** Empty lines will be replaced by `REM *` to maintain line numbering integrity.

*Disclaimer: This tool is provided as-is without any warranties. Always keep a backup of your original code before using the renumbering tool.*

Test it [here](https://chaozz.nl/github/renumber.php)
