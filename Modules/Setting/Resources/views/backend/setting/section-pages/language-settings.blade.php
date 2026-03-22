@extends('setting::backend.setting.index')

@section('title')
    {{ __('setting_sidebar.lbl_language') }}
@endsection

@section('settings-content')
    <div class="container p-0">
        <div class="col-md-12 d-flex justify-content-between mb-3">
            <h3><i class="fa fa-language"></i> {{ __('setting_sidebar.lbl_language') }}</h3>

        </div>

        {{-- ── Gestion du menu langue ─────────────────────────────────────── --}}
    <div class="card mb-4">
        <div class="card-header d-flex align-items-center gap-2">
            <i class="ph ph-globe fs-5"></i>
            <h6 class="mb-0">Gestion du menu langue</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('backend.settings.language-menu-settings.save') }}">
                @csrf

                {{-- Toggle affichage menu --}}
                <div class="d-flex align-items-center justify-content-between p-3 rounded mb-3"
                     style="background:rgba(55,138,221,0.08);border:1px solid rgba(55,138,221,0.2)">
                    <div>
                        <div class="fw-semibold">Afficher le sélecteur de langue</div>
                        <small class="text-muted">Affiche ou masque le menu de sélection de langue dans l'interface client</small>
                    </div>
                    <div class="form-check form-switch ms-3">
                        <input class="form-check-input" type="checkbox" name="language_menu_enabled"
                               id="language_menu_enabled" role="switch"
                               {{ ($languageMenuEnabled ?? '1') == '1' ? 'checked' : '' }}
                               style="width:2.5rem;height:1.3rem">
                    </div>
                </div>

                {{-- Langues actives --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold mb-2">Langues actives dans le menu</label>
                    <small class="text-muted d-block mb-3">Seules les langues cochées seront affichées dans le menu de sélection</small>
                    <div class="row g-2">
                        @foreach($languages as $lang)
                        @php
                            $flagSrc = asset('flags/' . $lang['id'] . '.png');
                            $isEnabled = in_array($lang['id'], $enabledLanguages ?? array_column($languages, 'id'));
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3">
                            <label class="d-flex align-items-center gap-3 p-3 rounded cursor-pointer"
                                   style="background:{{ $isEnabled ? 'rgba(29,158,117,0.1);border:1px solid rgba(29,158,117,0.25)' : 'rgba(128,128,128,0.08);border:1px solid rgba(128,128,128,0.15)' }};transition:.15s">
                                <input type="checkbox" name="enabled_languages[]" value="{{ $lang['id'] }}"
                                       class="form-check-input flex-shrink-0"
                                       {{ $isEnabled ? 'checked' : '' }}
                                       style="width:1.1rem;height:1.1rem">
                                <img src="{{ $flagSrc }}" width="22" alt="{{ $lang['id'] }}"
                                     onerror="this.src='{{ asset('flags/globe.png') }}'">
                                <div>
                                    <div class="fw-semibold small">{{ strtoupper($lang['id']) }}</div>
                                    <div class="text-muted" style="font-size:11px">{{ $lang['name'] }}</div>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="ph ph-floppy-disk me-1"></i>{{ __('messages.save') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Éditeur de traductions ───────────────────────────────────────── --}}
    <form id="form-submit" method="POST" class="requires-validation" novalidate>
            @csrf
            <div class="container p-0">
                <div class="row gy-3">
                    <div class="col">
                        <label class="form-label">{{ __('setting_language_page.lbl_language') }}<span
                                class="text-danger">*</span></label>
                        <select id="language_id" name="language_id" class="form-control select2" required>
                            <option value="" disabled {{ old('language_id') ? '' : 'selected' }}>
                                {{ __('placeholder.lbl_select_language') }}</option>
                            @foreach ($languages as $language)
                                <option value="{{ $language['id'] }}"
                                    {{ old('language_id', 'en') == $language['id'] ? 'selected' : '' }}>
                                    {{ $language['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="language_id-error">Language field is required</div>
                    </div>
                    <div class="col">
                        <label class="form-label">{{ __('setting_language_page.lbl_file') }}<span
                                class="text-danger">*</span></label>
                        <select id="file_id" name="file_id" class="form-control select2">
                            <option value="" disabled {{ old('file_id') ? '' : 'selected' }}>
                                {{ __('messages.lbl_select_file') }}</option>
                            <!-- Options will be dynamically loaded here -->
                        </select>
                        <div class="invalid-feedback" id="file_id-error">File field is required</div>
                    </div>
                </div>
            </div>


            <div class="container p-0 mt-3">
                <div class="row">
                    <div class="col">
                        <h6>
                            <label class="form-label">{{ __('setting_language_page.lbl_key') }}</label>
                        </h6>
                    </div>
                    <div class="col">
                        <h6>
                            <label class="form-label">{{ __('setting_language_page.lbl_value') }}</label>
                        </h6>
                    </div>
                </div>

                <div class="container p-0" id="translation-keys">
                    <!-- Translation keys will be dynamically loaded here -->
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary" id="submit-button">{{ __('messages.save') }}</button>
            </div>
        </form>
    </div>
@endsection
@push('after-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            $('.select2').select2();

            $('#language_id').on('change', function() {
                var languageId = $(this).val();
                fetchFiles(languageId);
            });
            $('#file_id').on('change', function() {
                var languageId = document.getElementById('language_id').value;
                var fileId = $(this).val();
                fetchLangData(fileId, languageId);

            });
            const languageId = document.getElementById('language_id').value;
            if (languageId) {
                fetchFiles(languageId).then(() => {
                    const fileId = document.getElementById('file_id').value;
                    if (fileId) {
                        fetchLangData(fileId, languageId);
                    }
                });
            }

            document.getElementById('language_id').addEventListener('change', function() {
                var languageId = this.value;
                fetchFiles(languageId);
            });

            document.getElementById('file_id').addEventListener('change', function() {
                var fileId = this.value;
                var languageId = document.getElementById('language_id').value;
                fetchLangData(fileId, languageId);
            });





            function fetchFiles(languageId) {
                fetch(`{{ route('backend.languages.array_list') }}?language_id=${languageId}`)
                    .then(response => response.json())
                    .then(data => {
                        let fileSelect = document.getElementById('file_id');
                        fileSelect.innerHTML = '<option value="">{{ __('Select File') }}</option>';
                        data.forEach(file => {
                            let option = document.createElement('option');
                            option.value = file.id;
                            option.textContent = file.name;
                            fileSelect.appendChild(option);
                        });
                        if (data.length > 0) {
                            fileSelect.value = data[0].id;
                            $(fileSelect).trigger('change'); // Trigger the change event to fetch language data
                            var languageId = document.getElementById('language_id').value;
                            var fileId = fileSelect.value;
                            // fetchLangData(fileId,languageId);
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching files:', error);
                        // Handle error if needed
                    });
            }

            function fetchLangData(fileId, languageId) {
                fetch(`{{ route('backend.languages.get_file_data') }}?file_id=${fileId}&language_id=${languageId}`)
                    .then(response => response.json())
                    .then(data => {
                        let container = document.getElementById('translation-keys');
                        container.innerHTML = '';
                        data.forEach(item => {
                            let row = document.createElement('div');
                            row.className = 'row';

                            let keyCol = document.createElement('div');
                            keyCol.className = 'col';
                            let keyGroup = document.createElement('div');
                            keyGroup.className = 'form-group';
                            let keyInput = document.createElement('input');
                            keyInput.type = 'text';
                            keyInput.className = 'form-control';
                            keyInput.value = item.key;
                            keyInput.disabled = true;
                            keyGroup.appendChild(keyInput);
                            keyCol.appendChild(keyGroup);

                            let valueCol = document.createElement('div');
                            valueCol.className = 'col';
                            let valueGroup = document.createElement('div');
                            valueGroup.className = 'form-group';
                            let valueInput = document.createElement('input');
                            valueInput.type = 'text';
                            valueInput.name = `lang_data[${item.key}]`;
                            valueInput.className = 'form-control';
                            valueInput.value = item.value;
                            valueGroup.appendChild(valueInput);
                            valueCol.appendChild(valueGroup);

                            row.appendChild(keyCol);
                            row.appendChild(valueCol);

                            container.appendChild(row);
                        });
                    });
            }
        });



        function submitForm(event) {
            event.preventDefault(); // Prevent the default form submission behavior

            // Get submit button and store original HTML
            const submitButton = document.getElementById('submit-button');
            const originalButtonHTML = submitButton.innerHTML;

            // Get language_id and file_id
            let languageId = document.getElementById('language_id').value;
            let fileId = document.getElementById('file_id').value;

            // Clear previous error messages
            document.getElementById('language_id').classList.remove('is-invalid');
            document.getElementById('file_id').classList.remove('is-invalid');
            document.getElementById('language_id-error').style.display = 'none';
            document.getElementById('file_id-error').style.display = 'none';

            let hasError = false;

            // Validate language_id
            if (!languageId) {
                document.getElementById('language_id').classList.add('is-invalid');
                document.getElementById('language_id-error').style.display = 'block';
                hasError = true;
            }

            // Validate file_id
            if (!fileId) {
                document.getElementById('file_id').classList.add('is-invalid');
                document.getElementById('file_id-error').style.display = 'block';
                hasError = true;
            }

            if (hasError) {
                return; // Stop form submission if validation fails
            }

            // Show loading state
            submitButton.disabled = true;
            submitButton.innerText = '{{ __('messages.loading') }}...';

            // Initialize an array to hold the formatted data
            let formattedData = [];

            // Get all input fields inside #translation-keys
            let dataInputs = document.querySelectorAll('#translation-keys input[type="text"]');

            // Iterate over each input field
            dataInputs.forEach(input => {
                // Extract key and value from input name and value
                let key = input.name.replace('lang_data[', '').replace(']', '');
                let value = input.value;
                // Skip if key or value is empty
                if (!key || !value) {
                    return;
                }

                // Construct the data object with key, value, languageId, and fileId
                let dataObj = {
                    key: key,
                    value: value,
                    language: languageId,
                    file: fileId
                };

                // Push the data object to formattedData array
                formattedData.push(dataObj);
            });

            // Prepare the payload as JSON
            const payload = JSON.stringify({
                language_id: languageId,
                file_id: fileId,
                data: formattedData
            });

            // Send the FormData via fetch
            fetch('{{ route('backend.languages.store') }}', {
                    method: 'POST',
                    body: payload,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ ok, data }) => {
                    // Restore button state
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHTML;
                    if (ok && data.status && data.message) {
                        window.successSnackbar(data.message);
                    } else if (!ok && data.message) {
                        window.errorSnackbar(data.message);
                    } else if (ok) {
                        window.successSnackbar('{{ __('setting_sidebar.lbl_language') }} updated successfully.');
                    } else {
                        window.errorSnackbar(data.message || 'Something went wrong, please check');
                    }
                })
                .catch(error => {
                    // Restore button state on error
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonHTML;
                    window.errorSnackbar('Something went wrong, please check');
                });
        }

        $("#submit-button").click(function(e) {
            e.preventDefault();
            submitForm(e);
        });
    </script>
@endpush
