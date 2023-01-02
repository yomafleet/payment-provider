<?php
    $signature_string = create_signature_string($post);
    $hash_value = hash_hmac('sha1', $signature_string, $config['secret'], false);
    $hash_value = strtoupper($hash_value);

    function create_signature_string($post)
    {
        sort($post, SORT_STRING);

        $signature_string = "";

        foreach ($post as $value) {
            if ($value != "") {

                $signature_string .= $value;
            }
        }
        
        return $signature_string;
    }
?>

<!-- Create a form that contains hidden fields whose names and values are identical to the ones from $_POST
        as well as new hidden field hashValue.
    From JS, automatically submit the hidden form (POST).
    In case JS is disabled, show a visible Submit button with msg 
        like "Click here if the site is taking too long to redirect!" -->

<html>
<head>
</head>

<body>
<h1>Redirecting to MPU Payment Gateway ...</h1>

<form id="hidden_form" name="hidden_form" method="post" action="<?php echo $config['gateway_url']; ?>">
    
    <input type="submit" value="Click here if it is taking too long to redirect!"/>
    <div style="visibility:hidden">
        <?php foreach($post as $key => $value): ?>
        <?php if ($value != ""): ?>
        <label><?php echo htmlspecialchars($key); ?></label>
        <input type="text" name="<?php echo htmlspecialchars($key); ?>"
               value="<?php echo htmlspecialchars($value); ?>"/>
        <br/>

        <?php endif; ?>
        <input type="text" value="<?php echo create_signature_string($post);?>">
        <?php endforeach; ?>
        <input type="text" name="hashValue" value="<?php echo $hash_value; ?>"/>
        <br/>
    </div>
</form>


<script>
    function submitForm() {
        document.forms["hidden_form"].submit();
    }

    if (window.attachEvent) {
        window.attachEvent("onload", submitForm);
    }
    else {
        window.addEventListener("load", submitForm, false);
    }
</script>
</body>

</html>