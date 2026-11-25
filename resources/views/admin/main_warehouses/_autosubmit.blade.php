{{-- A filter marked data-autosubmit reloads the list as soon as its value changes. --}}
<script>
    document.querySelectorAll('select[data-autosubmit]').forEach((field) => {
        field.addEventListener('change', () => field.form.requestSubmit());
    });
</script>
