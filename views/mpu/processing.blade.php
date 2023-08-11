<!DOCTYPE html>
<html lang='en'>

<head>
    <title>MPU Processing</title>
</head>

<body>
    <h1>Redirecting to MPU Payment Gateway ...</h1>

    <form id="hidden_form" name="hidden_form" method="post" action="<?php echo $url; ?>">

        <input type="submit" value="Click here if it is taking too long to redirect!" />
        <div style="visibility:hidden">
            <?php foreach($post as $key => $value): ?>
            <?php if ($value != ""): ?>
            <label><?php echo htmlspecialchars($key); ?></label>
            <input
                type="text"
                name="<?php echo htmlspecialchars($key); ?>"
                value="<?php echo htmlspecialchars($value); ?>"
            >
            <br />
            <?php endif; ?>
            <?php endforeach; ?>
            <br />
        </div>
    </form>


    <script>
        function submitForm() {
            document.forms["hidden_form"].submit();
        }

        if (window.attachEvent) {
            window.attachEvent("onload", submitForm);
        } else {
            window.addEventListener("load", submitForm, false);
        }
    </script>
</body>

</html>
