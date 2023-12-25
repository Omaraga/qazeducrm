$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
const targetNode = document.body;
const config = { childList: true, subtree: true };

const callback = function(mutationsList, observer) {
    for(let mutation of mutationsList) {
        if (mutation.type === 'childList') {
            let modal = document.getElementById('w0-sorting-modal');
            if (modal){
                modal.removeAttribute('id');
                observer.disconnect();
            }
        }
    }
};

const observer = new MutationObserver(callback);
observer.observe(targetNode, config);

$('#modalButton').click(function (e){
    $('#modal-form').find('#modalContent').load($(this).attr('value'));
    $('#modal-form').modal('show')
})