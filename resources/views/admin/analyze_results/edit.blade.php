@extends('admin.layouts.app')

@section('title')
    <title>Медицинская система | Редактировать результаты</title>
@endsection

@section('content')
    <h6 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light"><a class="text-muted"
                                             href="{{ route('analyzes.index') }}">Анализы</a> /<a class="text-muted"
                                             href="{{ route('analyze.result.show', $analyze->id) }}">Результаты анализа</a> /</span>Редактировать
        результаты
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
            <h5 class="mb-0">Редактировать результаты</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('analyze.result.update', $analyze->id) }}" method="POST"
                  enctype="multipart/form-data">
                @csrf
                @method('put')

                <div class="col">
                    @foreach($analyze->results as $key => $result)
                        <div class="mb-5">
                            <h6>{{ $result->type->name }}</h6>

                            <input type="hidden" name="result[{{$key}}][type_id]" value="{{ $result->type_id }}">
                            <div class="row">
                                <!-- Unit -->
                                <div class="col-sm-4">
                                    <label for="unit" class="form-label">Единицы измерения <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text @error('unit') text-danger icon-border @enderror"><i
                                                class="bx bx-barcode"></i></span>
                                        <input type="text" id="unit"
                                               class="form-control @error('unit') is-invalid @enderror"
                                               name="result[{{$key}}][unit]"
                                               placeholder="Единицы измерения" value="{{ old('unit', $result->unit) }}"
                                               required/>
                                    </div>
                                    @error('unit')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Value -->
                                <div class="col-sm-4">
                                    <label for="value" class="form-label">Значение <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span
                                            class="input-group-text @error('value') text-danger icon-border @enderror"><i
                                                class="bx bx-calculator"></i></span>
                                        <input type="number" id="value"
                                               class="form-control @error('value') is-invalid @enderror"
                                               name="result[{{$key}}][value]"
                                               placeholder="Значение" value="{{ old('value',$result->value) }}"
                                               required/>
                                    </div>
                                    @error('value')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <!-- Reference Range -->
                                <div class="col-sm-4 mb-4">
                                    <label for="reference_range" class="form-label">Референсные значения <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-merge">
                                        <span
                                            class="input-group-text @error('reference_range') text-danger icon-border @enderror"><i
                                                class="bx bx-line-chart"></i></span>
                                        <input type="number" id="reference_range"
                                               class="form-control @error('reference_range') is-invalid @enderror"
                                               name="result[{{$key}}][reference_range]"
                                               placeholder="Референсные значения"
                                               value="{{ old('reference_range', $result->reference_range) }}" required/>
                                    </div>
                                    @error('reference_range')
                                    <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label" for="basic-icon-default-comment">Комментарии</label>
                                    <div class="input-group input-group-merge">
                                <span id="basic-icon-default-history2"
                                      class="input-group-text @error('comment') text-danger icon-border @enderror">
                                    <i class="bx bx-notepad"></i>
                                </span>
                                        <textarea id="basic-icon-default-comment"
                                                  class="form-control @error('comment') is-invalid @enderror"
                                                  placeholder="Комментарии"
                                                  name="result[{{$key}}][comment]"
                                                  aria-describedby="basic-icon-default-history2">{{ old('comment', $result->comment) }}</textarea>
                                    </div>
                                    @error('comment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-warning">Редактировать</button>
                </div>
            </form>
        </div>
    </div>
@endsection
