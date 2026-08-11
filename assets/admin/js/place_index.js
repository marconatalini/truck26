document.addEventListener('DOMContentLoaded', function(){
    const rows = document.querySelectorAll('[data-index-row-warning = "true"]');
    rows.forEach(row => {
        row.classList.add('table-warning');
    })
})
