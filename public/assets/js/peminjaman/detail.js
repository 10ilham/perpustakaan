document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.return-btn').forEach(button => {
        button.addEventListener('click', function() {
            const actionUrl = this.getAttribute('data-action');
            document.getElementById('return-form').setAttribute('action', actionUrl);
        });
    });

    // Handler untuk tombol pengambilan
    document.querySelectorAll('.btn-success-pengambilan').forEach(button => {
        button.addEventListener('click', function() {
            const actionUrl = this.getAttribute('data-action');
            document.getElementById('pengambilan-form').setAttribute('action', actionUrl);
        });
    });
});
