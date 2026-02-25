<script src="https://code.jquery.com/jquery-3.5.1.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.1/js/dataTables.bootstrap5.min.js"></script>

<script>
    var start_date = '';
    var end_date = '';
    var data_per_fetch = 500;
    var data_fetched = 0;
    var current_page = 1;
    var total_count = 0;
    var has_more_data = false;

    $(document).ready(function() {
        $('#table').DataTable({
            searching: false,
            ordering: false,
            info: false,
            paging: false,
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
        current_page = 1;
        data_fetched = 0;
        getData()
    })

    function getData() {

        $('#loading-filter').show();
        var dataTableObj = $('#table').DataTable();
        var filter_kode = $('#filter-kode').val()
        var filter_nama = $('#filter-nama').val()
        var filter_harga_min = $('#filter-harga-min').val()
        var filter_harga_max = $('#filter-harga-max').val()
        var filter_start_date = $('#filter-start-date').val()
        var filter_end_date = $('#filter-end-date').val()
        var show_deleted = $('#show_deleted').is(':checked');
        
        // Update global variables
        start_date = filter_start_date;
        end_date = filter_end_date;

        if (current_page === 1) {
            dataTableObj.clear().draw();
        }

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
                start_date: start_date,
                end_date: end_date,
                show_deleted: show_deleted,
                page: current_page,
                data_per_fetch: data_per_fetch
            },
            success: function(results) {
                var data = results.data
                total_count = results.total_count;
                
                if (data.length > 0) {
                    has_more_data = true;
                    data_fetched += data.length;
                    
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

                        dataTableObj.row.add(array_temp).draw(false);
                    });
                    
                    // Check if there's more data to load
                    if (data_fetched >= total_count) {
                        has_more_data = false;
                    }
                    
                    // Auto-load next page if there's more data
                    if (has_more_data) {
                        current_page++;
                        setTimeout(function() {
                            getData();
                        }, 500);
                    }
                } else {
                    has_more_data = false;
                }
                
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
