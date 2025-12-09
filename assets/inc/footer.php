<footer class="footer">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <?php echo date('Y'); ?> &copy; OOU Hospital Management System. Developed By OOU ICT</a>
            </div>

        </div>
    </div>
</footer>
<!-- Toast container -->
<div aria-live="polite" aria-atomic="true" style="position: fixed; top: 1rem; right: 1rem; z-index: 1060;">
    <div id="toastContainer"></div>
</div>

<script>
function showToast(type, message, timeout) {
    timeout = timeout || 3500;
    var container = document.getElementById('toastContainer');
    if (!container) return;
    var toastId = 'toast-'+Date.now();
    var wrapper = document.createElement('div');
    wrapper.innerHTML = '<div id="'+toastId+'" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="'+timeout+'">'
        + '<div class="toast-header '+(type==='success'?'bg-success text-white':(type==='danger'?'bg-danger text-white':'bg-secondary text-white'))+'">'
        + '<strong class="mr-auto">'+(type==='success'? 'Success': (type==='danger' ? 'Error' : 'Notice'))+'</strong>'
        + '<button type="button" class="ml-2 mb-1 close text-white" data-dismiss="toast" aria-label="Close"><span aria-hidden="true">&times;</span></button>'
        + '</div>'
        + '<div class="toast-body">'+(message||'')+'</div>'
        + '</div>';
    container.appendChild(wrapper);
    var $toast = jQuery('#'+toastId);
    if ($toast && $toast.toast) {
        $toast.toast('show');
        $toast.on('hidden.bs.toast', function(){ wrapper.remove(); });
    } else {
        // fallback alert
        alert(message);
    }
}
</script>