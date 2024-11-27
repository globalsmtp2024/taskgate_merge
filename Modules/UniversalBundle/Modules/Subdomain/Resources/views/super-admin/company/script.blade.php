<script>
    function updateDomainType() {
        let domainType = $("[name='domain']").val();
        if (domainType === '') {
            $("input[name='sub_domain']").siblings('.input-group-append').addClass('d-none');
            $("input[name='sub_domain']").parent().removeClass('input-group');
            $("input[name='sub_domain']").parent().addClass('form-group');
        } else {
            $("input[name='sub_domain']").siblings('.input-group-append').removeClass('d-none');
            $("input[name='sub_domain']").parent().addClass('input-group');
            $("input[name='sub_domain']").parent().removeClass('form-group');

        }
    }
    $(document).ready(function () {
        updateDomainType();
        $('.domain-type').on('change', function () {
            updateDomainType();
        });
    });
</script>
