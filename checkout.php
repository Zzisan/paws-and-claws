<script src="https://www.paypalobjects.com/api/checkout.js"></script>
<?php 
$total = 0;
$qry = $conn->query("SELECT c.*,p.product_name,i.size,i.price,p.id as pid from `cart` c inner join `inventory` i on i.id=c.inventory_id inner join products p on p.id = i.product_id where c.client_id = ".$_settings->userdata('id'));
while($row= $qry->fetch_assoc()):
    $price = !empty($row['price']) ? $row['price'] : 0;
    $quantity = !empty($row['quantity']) ? $row['quantity'] : 0;
    $total += $price * $quantity;
endwhile;
?>
<section class="py-5">
    <div class="container">
        <div class="card rounded-0">
            <div class="card-body"></div>
            <h3 class="text-center"><b>Checkout</b></h3>
            <hr class="border-dark">
            <form action="" id="place_order">
                <input type="hidden" name="amount" value="<?php echo number_format($total, 2, '.', ''); ?>">
                <input type="hidden" name="payment_method" value="cod">
                <input type="hidden" name="paid" value="0">
                <div class="row row-col-1 justify-content-center">
                    <div class="col-6">
                        <div class="form-group col">
                            <label for="" class="control-label">Delivery Address</label>
                            <textarea id="" cols="30" rows="3" name="delivery_address" class="form-control" style="resize:none"><?php echo $_settings->userdata('default_delivery_address') ?></textarea>
                        </div>
                        <div class="col">
                            <span><h4><b>Total:</b> $<?php echo number_format($total, 2) ?></h4></span>
                        </div>
                        <hr>
                        <div class="col my-3">
                            <h4 class="text-muted">Payment Method</h4>
                            <div class="d-flex w-100 justify-content-between">
                                <button class="btn btn-flat btn-dark">Cash on Delivery</button>
                                <span id="paypal-button"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    var total = parseFloat('<?php echo $total; ?>');
    if (isNaN(total)) {
        total = 0.00;  // Ensure total is a valid number
    }

    paypal.Button.render({
        env: 'sandbox', // change to 'production' for live app
        client: {
            sandbox: 'AdDNu0ZwC3bqzdjiiQlmQ4BRJsOarwyMVD_L4YQPrQm4ASuBg4bV5ZoH-uveg8K_l9JLCmipuiKt4fxn',
            // production: 'your-production-client-id'
        },
        commit: true, // Show a 'Pay Now' button
        style: {
            color: 'blue',
            size: 'small'
        },
        payment: function(data, actions) {
            return actions.payment.create({
                payment: {
                    transactions: [
                        {
                            amount: { 
                                total: total.toFixed(2),  // Format to 2 decimal places
                                currency: 'PHP'
                            }
                        }
                    ]
                }
            });
        },
        onAuthorize: function(data, actions) {
            return actions.payment.execute().then(function(payment) {
                payment_online();
            });
        },
    }, '#paypal-button');

    function payment_online(){
        $('[name="payment_method"]').val("Online Payment");
        $('[name="paid"]').val(1);
        $('#place_order').submit();
    }

    $(function(){
        $('#place_order').submit(function(e){
            e.preventDefault();
            start_loader();
            $.ajax({
                url: 'classes/Master.php?f=place_order',
                method: 'POST',
                data: $(this).serialize(),
                dataType: "json",
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function(resp) {
                    if (!!resp.status && resp.status == 'success') {
                        alert_toast("Order successfully placed.", 'success');
                        setTimeout(function() {
                            location.replace('./');
                        }, 2000);
                    } else {
                        console.log(resp);
                        alert_toast("An error occurred", 'error');
                        end_loader();
                    }
                }
            });
        });
    })
</script>
