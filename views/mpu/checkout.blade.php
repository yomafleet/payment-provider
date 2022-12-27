<html>

<head>
</head>

<body>
<h1>MPU e-Commerce Sample Checkout Page</h1>
<form class="form-horizontal" action="{{$config['callback']}}"
                              method="post">
                            <div style="visibility:hidden">
                                <input type="text" id="invoiceNo" name="invoiceNo" value="12345678901234567890"/>
                            </div>
                            <div class="box-body">
                                <div class="col-xs-6">
                                    <p class="lead">Payment Methods:</p>
                                    <p class="text-muted well well-sm no-shadow">
                                        We can use with every Bank of MPU Card.
                                    </p>
                                
                                
                                    <button class="btn btn-mgma" type="submit"><i class="fa fa-credit-card"></i> Submit Payment</button>
                                </div>
                                
                            </div>
                        </form>
    
    
<script>
        function padLeft(str, width, paddingChar) {
            paddingChar = paddingChar || '0';
            str = str + ''; // force conversion to string
            return str.length >= width ? str : new Array(width - str.length + 1).join(paddingChar) + str;
        }

        Date.prototype.yyyyMMddhhmmss = function () {
            var yyyy = this.getFullYear().toString();
            var MM = (this.getMonth() + 1).toString(); // getMonth() is zero-based
            var dd = this.getDate().toString();
            var hh = this.getHours().toString();
            var mm = this.getMinutes().toString();
            var ss = this.getSeconds().toString();
            return yyyy + padLeft(MM, 2) + padLeft(dd, 2)
                    + padLeft(hh, 2) + padLeft(mm, 2) + padLeft(ss, 2); // padding
        };

        function initPage() {
            var invoiceNo = new Date().yyyyMMddhhmmss();
            document.getElementById("invoiceNo").value = padLeft(invoiceNo, 20);
        }

        window.onload = initPage();
</script>

</body>

</html>