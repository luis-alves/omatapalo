<!DOCTYPE html>

<html>

    <head>
        <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
        <meta charset="utf-8">
        <title>dump</title>
    </head>

    <body>
        <?php for ($i = 0, $n = count($arguments); $i < $n; $i++): ?>
            <pre><?php print_r($arguments[$i]); ?></pre>
            <?php if ($i < $n - 1): ?>
                <hr/>
            <?php endif ?>
        <?php endfor ?>
    </body>

</html>
