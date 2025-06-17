@extends('admin.layouts.app')

@section('title')
    <title>MED ERA | Редактировать лабораторию</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('laboratories.index') }}">Лаборатории</a> /</span>Редактировать
    </h6>

    <div class="alert-container position-fixed top-0 end-0 p-3" style="z-index: 10050;">
        @if ($errors->any())
            @foreach ($errors->all() as $error)
                <div class="alert alert-solid-danger alert-dismissible d-flex align-items-center" role="alert">
                    <i class="bx bx-error-circle fs-4 me-2"></i>
                    {{ $error }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endforeach
        @endif
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Редактировать</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('laboratories.update', $laboratory->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-company">Название <span
                                class="text-danger">*</span></label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('name') text-danger icon-border @enderror"><i
                                    class="bx bx-rename"></i></span>
                            <input type="text" id="basic-icon-default-company"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="Название" aria-label="Название"
                                   name="name"
                                   value="{{ old('name', $laboratory->name) }}"
                                   aria-describedby="basic-icon-default-company2" required/>
                        </div>
                        @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-company">
                            Адрес
                        </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-company2"
                                  class="input-group-text @error('address') text-danger icon-border @enderror">
                                <i class="bx bx-map"></i>
                            </span>
                            <input type="text" id="basic-icon-default-company"
                                   class="form-control @error('address') is-invalid @enderror"
                                   placeholder="Адрес" aria-label="Адрес"
                                   name="address"
                                   value="{{ old('address', $laboratory->address) }}"
                                   aria-describedby="basic-icon-default-company2"/>
                        </div>

                        @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-phone">Телефон </label>
                        <div class="input-group input-group-merge">
                            <span id="basic-icon-default-phone2"
                                  class="input-group-text @error('phone') text-danger icon-border @enderror"><i
                                    class="bx bx-phone"></i></span>
                            <input type="text" id="basic-icon-default-phone"
                                   class="form-control phone @error('phone') is-invalid @enderror"
                                   name="phone"
                                   value="{{ old('phone', $laboratory->phone) }}"
                                   placeholder="+ 998 (XX) XXX XX XX" aria-label="+ 998 (XX) XXX XX XX"
                                   aria-describedby="basic-icon-default-phone2"/>
                        </div>

                        @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="basic-icon-default-address">Email</label>

                        <div class="input-group input-group-merge">
                            <span class="input-group-text @error('email') text-danger icon-border @enderror"><i
                                    class="bx bx-envelope"></i></span>
                            <input type="email" id="basic-icon-default-address"
                                   class="form-control @error('email') is-invalid @enderror" placeholder="Email"
                                   name="email"
                                   value="{{ old('email', $laboratory->email) }}"
                                   aria-label="Email" aria-describedby="basic-icon-default-email2"/>
                        </div>
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-warning ">Редактировать</button>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        $(document).ready(function () {
            const phoneInput = $('.phone');

            phoneInput.on('focus', function () {
                if (!$(this).val().startsWith('+ 998')) {
                    $(this).val('+ 998 ');
                }
            });

            phoneInput.mask('+ 998 (00) 000 00 00', {
                placeholder: '+ 998 (__) ___ __ __',
                clearIfNotMatch: true
            });

            phoneInput.on('blur', function () {
                if ($(this).val() === '+ 998 ') {
                    $(this).val('');
                }
            });
        })
    </script>
@endsection
