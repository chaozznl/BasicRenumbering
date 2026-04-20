<?php
    if (isset($_POST['start']))
    {
        $start = intval($_POST['start']); // start at this line number
        $increment = intval($_POST['increment']); // incremement by this amount
        $data = $_POST['data']; // sourcecode to renumber

        // now lets split the sourcecode into lines and get to work
        $lines = explode("\n", $data);
        $lineNumber = $start;

        // first we renumber
        foreach ($lines as $line)
        {
            if (preg_match('/^(\d+)(.*)$/', $line, $matchArr)) 
            {
                $index[$lineNumber] = (int)$matchArr[1]; // store the original line number for reference
                $code[$lineNumber] = trim($matchArr[2]);
            }
            else
            {
                $index[$lineNumber] = $lineNumber; // store the original line number for reference
                $code[$lineNumber] = "REM *";
            }

            $lineNumber += $increment;
        }

        // next we find references to line numbers and update them
        foreach ($code as $lineNumber => $codeline)
        {
            $instructions = explode(':', $codeline);

            foreach ($instructions as &$instruction)
            {
                // --- STEP 1: mask strings ---
                $masked = [];
                $placeholderIndex = 0;

                $instruction = preg_replace_callback(
                    '/"[^"]*"/',
                    function($match) use (&$masked, &$placeholderIndex) {
                        $key = "__STRING_PLACEHOLDER_" . $placeholderIndex++ . "__";
                        $masked[$key] = $match[0];
                        return $key;
                    },
                    $instruction
                );

                // --- STEP 2: apply your regexes safely ---

                // THEN <line>
                $instruction = preg_replace_callback(
                    '/\bTHEN\s*(\d+)/i',
                    function($match) use ($index) {
                        $old = (int)$match[1];
                        $new = array_search($old, $index, true);
                        return 'THEN ' . ($new === false ? $old : $new);
                    },
                    $instruction
                );

                // GOTO / GOSUB
                $instruction = preg_replace_callback(
                    '/(GOTO|GOSUB)\s*(\d+)/i',
                    function($match) use ($index) {
                        $keyword = $match[1];
                        $old = (int)$match[2];
                        $new = array_search($old, $index, true);
                        return $keyword . ' ' . ($new === false ? $old : $new);
                    },
                    $instruction
                );

                // ON X GOTO A,B,C
                $instruction = preg_replace_callback(
                    '/\bON\s+[^:]+?\b(GOTO|GOSUB)\s+([\d,]+)/i',
                    function($match) use ($index) {
                        $keyword = $match[1];
                        $list = explode(',', $match[2]);
                        $newList = [];

                        foreach ($list as $old) {
                            $old = (int)$old;
                            $new = array_search($old, $index, true);
                            $newList[] = ($new === false ? $old : $new);
                        }

                        return 'ON ... ' . $keyword . ' ' . implode(',', $newList);
                    },
                    $instruction
                );

                // --- STEP 3: restore strings ---
                foreach ($masked as $key => $value) {
                    $instruction = str_replace($key, $value, $instruction);
                }
            }

            $code[$lineNumber] = implode(':', $instructions);
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Commodore 64 BASIC Line Renumbering Tool</title>
<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 800px;
        margin: 40px auto;
        line-height: 1.6;
        color: #222;
    }
    h1 {
        text-align: center;
        margin-bottom: 5px;
    }
    .subtitle {
        text-align: center;
        font-size: 0.9em;
        color: #666;
        margin-bottom: 30px;
    }
    form label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
    }
    input[type="number"],
    textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        font-family: monospace;
        font-size: 14px;
        box-sizing: border-box;
    }
    input[type="checkbox"] {
        margin-top: 10px;
    }
    button {
        margin-top: 20px;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
    }
    .output-label {
        font-weight: bold;
        margin-top: 30px;
        display: block;
    }
</style>
</head>
<body>

<h1>Commodore 64 BASIC Line Renumbering Tool</h1>
<div class="subtitle">by E. Wenners, 2026</div>

<p>Paste your sourcecode into the textarea below, specify the starting line number and increment, and click <strong>Renumber</strong>. The tool will renumber your BASIC code while preserving the structure and references.</p>

<p><strong>Note:</strong> Empty lines will be replaced with <code>REM *</code> to maintain line numbering integrity.</p>

<p><em>Disclaimer: This tool is provided as-is without any warranties. Always keep a backup of your original code before using the renumbering tool.</em></p>

<p>More details and sourcecode available on Github:  
<a href="https://github.com/chaozznl/BasicRenumbering" target="_blank">
    https://github.com/chaozznl/BasicRenumbering
</a></p>

<form method="POST">
    <label for="start">Start:</label>
    <input type="number" id="start" name="start" 
           value="<?php echo $_POST['start'] ?? ''; ?>" required>

    <label for="increment">Increment:</label>
    <input type="number" id="increment" name="increment" 
           value="<?php echo $_POST['increment'] ?? ''; ?>" required>

    <label>
        <input type="checkbox" id="space" name="space" value="1"
               <?php echo !empty($_POST['space']) ? 'checked' : ''; ?>>
        Space between line number and code
    </label>

    <label for="data">Sourcecode:</label>
    <textarea name="data" rows="10" placeholder="Enter your data here..." required><?php 
        echo $_POST['data'] ?? '';
    ?></textarea>

    <button type="submit">Renumber</button>
</form>

<label class="output-label" for="renumbered">Renumbered Sourcecode:</label>
<textarea name="renumbered" rows="10" readonly placeholder="Renumbered code will appear here..."><?php
    foreach ($code as $lineNumber => $codeline)
        echo $lineNumber . (isset($_POST['space']) ? " " : "") . $codeline . "\n";
?></textarea>

</body>
</html>