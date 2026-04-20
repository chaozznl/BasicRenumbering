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
<form method="POST">
    <label for="start">Start:</label>
    <input type="number" id="start" name="start" value="<?php echo isset($_POST['start']) ? $_POST['start'] : ''; ?>" required>
    <br>
    <label for="increment">Increment:</label>
    <input type="number" id="increment" name="increment" value="<?php echo isset($_POST['increment']) ? $_POST['increment'] : ''; ?>" required>
    <br>
    <label for="space">Space between line number and code:</label>
    <input type="checkbox" id="space" name="space" value="1" <?php echo !empty($_POST['space']) ? 'checked' : ''; ?>>
    <br>
    <label for="data">Sourcecode:</label>
    <br>
    <textarea name="data" rows="10" cols="50" placeholder="Enter your data here..." required><?php echo isset($_POST['data']) ? $_POST['data'] : ''; ?></textarea>
    <br>
    <button type="submit">Renumber</button>
</form>
    <label for="renumbered">Renumbered Sourcecode:</label>
    <br>
    <textarea name="renumbered" rows="10" cols="50" placeholder="Renumbered code will appear here..." readonly><?php
    foreach ($code as $lineNumber => $codeline)
        echo $lineNumber . (isset($_POST['space']) ? " " : "") . $codeline . "\n";
?></textarea>
