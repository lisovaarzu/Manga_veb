function loadOrders() {
    $.get('/ajax/get_orders.php', function (data) {
        $('#orders-live').html(data);
    });
}

$(document).ready(function () {
    loadOrders();

    setInterval(function () {
        loadOrders();
    }, 3000);

    $(document).on('change', '.order-status-select', function () {
        var orderId = $(this).data('order-id');
        var status = $(this).val();

        $.post('/ajax/update_order_status.php', {
            order_id: orderId,
            status: status,
            csrf_token: CSRF_TOKEN
        }, function () {
            loadOrders();
        });
    });
});