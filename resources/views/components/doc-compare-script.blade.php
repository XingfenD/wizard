@push('script')
<script>
    $(function () {
        $('.wz-body').on('click', '[wz-doc-compare-submit]', function (e) {
            e.preventDefault();

            var compareUrl = '{{ wzRoute('doc:compare') }}';

            var doc1url = $(this).data('doc1');
            var doc2url = $(this).data('doc2');

            axios.all([
                axios.get(doc1url),
                axios.get(doc2url)
            ]).then(axios.spread(function (resp1, resp2) {
                var layerId = 'wz-frame-' + resp1.data.external_id + '-' + resp2.data.external_id;

                $.wz.dialogOpen(layerId, '@lang('document.document_differ')', function (iframeId) {
                    $.wz.dynamicFormSubmit(
                        'wz-compare-' + resp1.data.external_id + '-' + resp1.data.external_id,
                        'post',
                        compareUrl,
                        {
                            doc1: resp1.data.content,
                            doc2: resp2.data.content,
                            doc1title: resp1.data.title,
                            doc2title: resp2.data.title,
                            doc1p_page_external_id: resp1.data.p_page_external_id,
                            doc2p_page_external_id: resp2.data.p_page_external_id,
                            noheader: 1
                        },
                        iframeId
                    );
                });
            }));
        });
    });
</script>
@endpush