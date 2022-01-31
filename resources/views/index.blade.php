@extends('layouts.default')

@push('header-script')
    <style>
        .main {
            padding: 5rem;
        }
        .mt-3 {
            margin-top: 3rem !important;
        }
        .mb-3 {
            margin-bottom: 3rem !important;
        }
        .hidden {
            display: none;
        }
    </style>
@endpush

@section('contents')
<div class="main">
    <div class="row">
        <div class="col-md-12">
            <h3>CC Automated Image Rover</h3>
        </div>
    </div>
    <form id="submitForm">
        @csrf
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="car_type" class="label-control">Car Type</label>
                    <input type="text" class="form-control" name="car_type" id="car_type" value="">
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="product_name" class="label-control">Product Name</label>
                    <input type="text" class="form-control" name="product_name" id="product_name" value="">
                </div>
            </div>
        </div>

        <input type="hidden" name="attach_files" id="attach_files" value="">
        <input type="hidden" name="zip_file_name" id="zip_file_name" value="">
    </form>
    <div class="row mt-3">
        <div class="col-md-6">
            <div class="row">
                <div class="col-md-12">
                    <div class="row mb-20" id="file-dropzone">
                        <div class="col-md-12">
                            <form action="" class="dropzone" id="file-upload-dropzone">
                                {{ csrf_field() }}
                                <div class="dz-message">
                                    <h5> <i class="fa fa-cloud-upload"></i> Drag & Drop or</h5>
                                    <br />
                                    <span class="">Click to Upload</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <textarea class="form-control" name="image_file_queue" id="image_file_queue" rows="7"></textarea>
            </div>
        </div>
    </div>
    <div class="row mt-3">
        <div class="form-group">
            <button class="btn btn-success save" id="submit">Submit</button>
            <button class="btn btn-success save hidden" id="download">Download Files</button>
            <button class="btn btn-primary" id="reset">Reset</button>
        </div>
    </div>
</div>
@endsection

@push('footer-script')
<script type="text/javascript">

    Dropzone.autoDiscover = false;

    $(document).ready(function() {

        var ib_submit = $('.save');
        var uploadUrl = "{{ route('upload-files') }}";

        var ib_file = new Dropzone("#file-upload-dropzone",
            {
                addRemoveLinks: false,
                url: uploadUrl,
                uploadMultiple: true,
                parallelUploads: 100,
                maxFiles: 50
            }
        );

        ib_file.on("sending", function (file) {
            ib_submit.prop('disabled', true);

            // update files queue
            var fileNames = $('#image_file_queue').val();
            fileNames += file.name + '\n'

            $('#image_file_queue').html(fileNames);
        });

        ib_file.on("successmultiple", function (file, response) {

            ib_submit.prop('disabled', false);

            upload_resp = response;

            if (upload_resp.status == 'success') {

                if($('#attach_files').val() != '') {
                    var attach_files = JSON.parse($('#attach_files').val());
                    $('#attach_files').val(JSON.stringify(attach_files.concat(response.data)));
                } else {
                    $('#attach_files').val(JSON.stringify(response.data));
                }

            }
            else {
                toastr.error(upload_resp.msg);
            }

        });

        // Save button action
        $('#submit').click(function(e) {

            e.preventDefault();

            var url = "{{ route('store') }}";

            $.easyAjax({
                type: 'POST',
                url: url,
                container: '#submitForm',
                data: $('#submitForm').serialize(),
                success: function (response) {
                    if(response.status === 'success'){
                        // $('#zip_file_name').val(response.file_name);
                        // $('#submit').addClass('hidden');
                        // $('#download').removeClass('hidden');

                        var url = "{{ route('download', ':fileName') }}";
                        url = url.replace(':fileName', response.file_name);
                        location.href = url;
                    }
                }
            });
        });

        // Download button action
        $('#download').click(function(e) {

            e.preventDefault();

            var url = "{{ route('download', ':fileName') }}";
            url = url.replace(':fileName', $('#zip_file_name').val());

            location.href = url;

        });

        // Reset button action
        $('#reset').click(function(e) {
            e.preventDefault();

            var url = "{{ route('reset') }}";

            $.easyAjax({
                type: 'GET',
                url: url,
                success: function (response) {
                    if(response.status === 'success'){
                        location.reload();
                    }
                }
            })

        })

    });
</script>
@endpush
