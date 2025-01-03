@extends('template.layouts.app')

@section('title', 'Dashboard')

@section('content')
    {{-- Success and Error Messages --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="row">
        <div class="col-lg-12 col-12">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12">
                    <div class="card">
                        <span class="mask bg-primary opacity-10 border-radius-lg"></span>
                        <div class="card-body p-3 position-relative">
                            <div class="row">
                                <div class="col-8 text-start">
                                    <div
                                        class="icon icon-shape bg-white shadow text-center border-radius-2xl p-auto">
                                        <img src="{{ asset('template/img/small-logos/cloud-upload.svg') }}"
                                            class="avatar avatar-sm mt-2 text-white" alt="xd" />
                                    </div>
                                    <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                        {{ $data->total() }}</h5>
                                    <span class="text-white text-sm">Files Uploaded</span>
                                </div>
                                <div class="col-4">
                                    <div class="dropdown text-end mb-6">
                                        <a href="javascript:;" class="cursor-pointer"
                                            id="dropdownUsers1" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h text-white"></i>
                                        </a>
                                        <ul class="dropdown-menu px-2 py-3"
                                            aria-labelledby="dropdownUsers1">
                                            <li><a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Action</a></li>
                                            <li><a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Another
                                                    action</a></li>
                                            <li>
                                                <a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Something else
                                                    here</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-12 mt-4 mt-md-0">
                    <div class="card">
                        <span class="mask bg-dark opacity-10 border-radius-lg"></span>
                        <div class="card-body p-3 position-relative">
                            <div class="row">
                                <div class="col-8 text-start">
                                    <div
                                        class="icon icon-shape bg-white shadow text-center border-radius-2xl p-auto">
                                        <img src="{{ asset('template/img/small-logos/cloud-download.svg') }}"
                                            class="avatar avatar-sm mt-2 text-white" alt="xd" />
                                    </div>
                                    <h5 class="text-white font-weight-bolder mb-0 mt-3">
                                        357</h5>
                                    <span class="text-white text-sm">Click
                                        Events</span>
                                </div>
                                <div class="col-4">
                                    <div class="dropstart text-end mb-6">
                                        <a href="javascript:;" class="cursor-pointer"
                                            id="dropdownUsers2" data-bs-toggle="dropdown"
                                            aria-expanded="false">
                                            <i class="fa fa-ellipsis-h text-white"></i>
                                        </a>
                                        <ul class="dropdown-menu px-2 py-3"
                                            aria-labelledby="dropdownUsers2">
                                            <li><a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Action</a></li>
                                            <li><a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Another
                                                    action</a></li>
                                            <li>
                                                <a class="dropdown-item border-radius-md"
                                                    href="javascript:;">Something else
                                                    here</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="row my-4">
        <div class="col-lg-12 col-md-8 mb-md-0 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-lg-6">
                            <h6>My Files</h6>
                        </div>
                        <div class="col-lg-6">
                            <button type="button" class="btn btn-primary btn-sm float-end"
                                onclick="showModal()">
                                Upload
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th
                                        class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Filename</th>
                                    <th
                                        class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($data as $item)
                                    @php
                                        $mimeMap = [
                                            'application/pdf' => 'pdf',
                                            'image/jpeg' => 'jpg',
                                            'image/png' => 'png',
                                            'text/plain' => 'txt',
                                            'application/vnd.ms-excel' => 'xls',
                                            'application/zip' => 'zip',
                                        ];

                                        $extension = $mimeMap[$item->mime_type] ?? 'unknown';
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div>
                                                    <img src="{{ asset('template/img/small-logos/filetype-' . $extension . '.svg') }}"
                                                        class="me-3" alt="xd" />
                                                </div>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $item->file_name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <div
                                                class="d-flex justify-content-center align-items-center">
                                                <a type="button"
                                                    class="btn btn-outline-primary btn-sm me-2"
                                                    href="{{ route('files.download', $item->id) }}"
                                                    target="_blank">
                                                    Download
                                                </a>
                                                <button type="button"
                                                    class="btn btn-outline-danger btn-sm"
                                                    onclick="confirmDelete('{{ $item->id }}')">
                                                    Delete
                                                </button>
                                                <form id="delete-form-{{ $item->id }}"
                                                    action="{{ route('files.destroy', $item->id) }}"
                                                    method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">No Data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script>
        function showModal() {
            Swal.fire({
                title: '<h4 style="margin-bottom: 20px; align-items: left;">Upload File</h4>',
                html: `
                <div style="display: flex; flex-direction: column; align-items: center;">
                    <input type="file" name="file" id="fileInput" style="margin-bottom: 20px; border: 1px solid #ccc; padding: 5px; border-radius: 5px; width: 80%;">
                </div>
            `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Upload',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#F97316',
                cancelButtonColor: '#71717A',
                preConfirm: () => {
                    const fileInput = document.getElementById('fileInput');
                    if (!fileInput.files.length) {
                        Swal.showValidationMessage('You must select a file!');
                        return false;
                    }
                    return fileInput.files[0];
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const file = result.value;

                    // Create a FormData object to send the file to the server
                    const formData = new FormData();
                    formData.append('file', file);

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')
                        .getAttribute('content');

                    // Send the file to FileUploadController.store using Fetch API
                    fetch('/files', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken // Tambahkan CSRF token di header
                            },
                            body: formData,
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Display success message
                            Swal.fire('Success!', `You uploaded: ${file.name}`, 'success');
                        })
                        .catch(error => {
                            // Display error message
                            Swal.fire('Error!', 'File upload failed!', 'error');
                            console.error('Error:', error);
                        });
                } else {
                    console.log('File upload canceled');
                }
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request ke route destroy
                    document.getElementById(`delete-form-${id}`).submit();
                }
            });
        }
    </script>

@endsection
