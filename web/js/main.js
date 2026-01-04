$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
$('#modal-form').removeAttr('tabindex')
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
});

// Table scroll indicators
document.addEventListener('DOMContentLoaded', function() {
    const tableContainers = document.querySelectorAll('.table-container-scrollable');

    tableContainers.forEach(container => {
        function updateScrollIndicator() {
            const hasHorizontalScroll = container.scrollWidth > container.clientWidth;
            const isScrolledToEnd = container.scrollLeft + container.clientWidth >= container.scrollWidth - 10;

            if (hasHorizontalScroll && !isScrolledToEnd) {
                container.classList.add('has-scroll');
            } else {
                container.classList.remove('has-scroll');
            }

            // For left scroll indicator
            if (container.scrollLeft > 10) {
                container.classList.add('scrolled-right');
            } else {
                container.classList.remove('scrolled-right');
            }
        }

        updateScrollIndicator();
        container.addEventListener('scroll', updateScrollIndicator);
        window.addEventListener('resize', updateScrollIndicator);
    });
});