<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    var start_date = '';
    var end_date = '';
    var data_per_fetch = 500;
    var data_fetched = 0;

    $(document).ready(function() {
        $('#table').DataTable({
            searching: false,
            order: [
                [0, 'desc']
            ],
            columnDefs: [{
                    width: "10%",
                    targets: 6
                } // Atur lebar kolom photo
            ]
        });
        getData()
    });

    $('.btn-get-data').click(function() {
        getData()
    })

    function getData() {

        $('#loading-filter').show();
        var dataTableObj = $('#table').DataTable();
        var filter_kode = $('#filter-kode').val()
        var filter_nama = $('#filter-nama').val()
        var filter_harga_min = $('#filter-harga-min').val()
        var filter_harga_max = $('#filter-harga-max').val()
        var show_deleted = $('#show_deleted').is(':checked');
        dataTableObj.clear().draw();

        $.ajax({
            url: '{{ url('master-items/search') }}',
            dataType: 'json',
            tryCount: 0,
            retryLimit: 3,
            data: {
                kode: filter_kode,
                nama: filter_nama,
                hargamin: filter_harga_min,
                hargamax: filter_harga_max,
                show_deleted: show_deleted
            },
            success: function(results) {
                var data = results.data

                $.each(data, function(index, item) {
                    var harga_jual = item.harga_beli + item.harga_beli * item.laba / 100;
                    harga_jual = Math.round(harga_jual)
                    var kode = item.kode;

                    // Buat HTML untuk photo
                    var photoHtml = '-';
                    if (item.photo) {
                        photoHtml =
                            `<img src="{{ asset('storage/photos/') }}/${item.photo}" width="50" height="50" style="object-fit: cover; border-radius: 5px;">`;
                    }

                    var actions = '';
                    if (item.is_deleted) {
                        actions = `
                            <button class="btn btn-success btn-sm btn-restore" data-id="${item.id}">Restore</button>
                            <button class="btn btn-danger btn-sm btn-force-delete" data-id="${item.id}">Hapus Permanen</button>
                        `;
                    } else {
                        actions =
                            `<a href="{{ url('master-items/view/') }}/${kode}" class="btn btn-primary btn-sm">View</a>`;
                    }

                    var array_temp = [
                        item.kode,
                        item.nama,
                        item.nama_kategori || '-',
                        item.jenis,
                        formatRupiah(item.harga_beli),
                        formatRupiah(harga_jual),
                        item.supplier,
                        photoHtml,
                        actions
                    ];

                    dataTableObj.row.add(array_temp).draw(true);
                });
                $('#loading-filter').hide();
            },
            error: function(xhr, textStatus, errorThrown) {
                this.tryCount++;
                if (this.tryCount <= this.retryLimit) {
                    $.ajax(this);
                    return;
                }
                alert('Terjadi kesalahan server, tidak dapat mengambil data')
                $('#loading-filter').hide();

                return;
            }
        })
    }

    $(document).on('click', '.btn-restore', function() {
        var id = $(this).data('id');
        if (confirm('Yakin ingin mengembalikan data ini?')) {
            var form = $('<form action="{{ url('master-items/restore') }}/' + id +
                '" method="POST">@csrf</form>');
            $('body').append(form);
            form.submit();
        }
    });

    $(document).on('click', '.btn-force-delete', function() {
        var id = $(this).data('id');
        if (confirm('Yakin ingin menghapus permanen data ini? File foto juga akan dihapus.')) {
            var form = $('<form action="{{ url('master-items/force-delete') }}/' + id +
                '" method="POST">@csrf</form>');
            $('body').append(form);
            form.submit();
        }
    });

    // Fungsi helper untuk format rupiah
    function formatRupiah(angka) {
        if (angka) {
            return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }
        return '0';
    }
</script>
