@extends('setting::backend.setting.index')

@section('title')
    {{ __('setting_sidebar.lbl_player_setting') }}
@endsection

@section('settings-content')
    <form method="POST" action="{{ route('backend.setting.store') }}" id="form-submit">
        @csrf
        <input type="hidden" name="setting_tab" value="player">

        {{-- Lecture --}}
        <div class="card mb-4">
            <div class="card-header p-0 mb-4">
                <h3 class="mb-0"><i class="fas fa-play-circle"></i> {{ __('messages.player_section_playback') }}</h3>
            </div>
            <div class="card-body p-0">

                {{-- Autoplay --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label m-0" for="player_autoplay">{{ __('messages.player_autoplay') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_autoplay_help') }}</small>
                        </div>
                        <input type="hidden" value="0" name="player_autoplay">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" value="1" name="player_autoplay" id="player_autoplay"
                                type="checkbox" {{ old('player_autoplay', $settings['player_autoplay'] ?? 0) == 1 ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>

                {{-- Start Muted --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label m-0" for="player_muted">{{ __('messages.player_muted') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_muted_help') }}</small>
                        </div>
                        <input type="hidden" value="0" name="player_muted">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" value="1" name="player_muted" id="player_muted"
                                type="checkbox" {{ old('player_muted', $settings['player_muted'] ?? 0) == 1 ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>

                {{-- Default Volume --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label" for="player_default_volume">{{ __('messages.player_default_volume') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_default_volume_help') }}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                {{ html()->number('player_default_volume', old('player_default_volume', $settings['player_default_volume'] ?? 80))
                                    ->class('form-control')
                                    ->attribute('min', 0)
                                    ->attribute('max', 100)
                                    ->id('player_default_volume') }}
                                <span class="input-group-text">%</span>
                            </div>
                            @error('player_default_volume')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Forward Seconds --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label" for="forward_seconds">{{ __('messages.lbl_forward_seconds') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_forward_seconds_help') }}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                {{ html()->number('forward_seconds', old('forward_seconds', $settings['forward_seconds'] ?? 30))
                                    ->class('form-control')
                                    ->attribute('min', 1)
                                    ->attribute('max', 300)
                                    ->id('forward_seconds') }}
                                <span class="input-group-text">s</span>
                            </div>
                            @error('forward_seconds')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Backward Seconds --}}
                <div class="form-group pb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label" for="backward_seconds">{{ __('messages.lbl_backward_seconds') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_backward_seconds_help') }}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                {{ html()->number('backward_seconds', old('backward_seconds', $settings['backward_seconds'] ?? 30))
                                    ->class('form-control')
                                    ->attribute('min', 1)
                                    ->attribute('max', 300)
                                    ->id('backward_seconds') }}
                                <span class="input-group-text">s</span>
                            </div>
                            @error('backward_seconds')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Interface --}}
        <div class="card mb-4">
            <div class="card-header p-0 mb-4">
                <h3 class="mb-0"><i class="fas fa-palette"></i> {{ __('messages.player_section_interface') }}</h3>
            </div>
            <div class="card-body p-0">

                {{-- Subtitles by default --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label m-0" for="player_show_subtitles">{{ __('messages.player_show_subtitles') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_show_subtitles_help') }}</small>
                        </div>
                        <input type="hidden" value="0" name="player_show_subtitles">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" value="1" name="player_show_subtitles" id="player_show_subtitles"
                                type="checkbox" {{ old('player_show_subtitles', $settings['player_show_subtitles'] ?? 0) == 1 ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>

                {{-- Picture-in-Picture --}}
                <div class="form-group border-bottom pb-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <label class="form-label m-0" for="player_pip">{{ __('messages.player_pip') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_pip_help') }}</small>
                        </div>
                        <input type="hidden" value="0" name="player_pip">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" value="1" name="player_pip" id="player_pip"
                                type="checkbox" {{ old('player_pip', $settings['player_pip'] ?? 1) == 1 ? 'checked' : '' }} />
                        </div>
                    </div>
                </div>

                {{-- Accent Color --}}
                <div class="form-group pb-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label class="form-label" for="player_color">{{ __('messages.player_color') }}</label>
                            <small class="text-muted d-block">{{ __('messages.player_color_help') }}</small>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group">
                                <input type="color" id="player_color_picker" class="form-control form-control-color p-1"
                                    value="{{ old('player_color', $settings['player_color'] ?? '#e50914') }}"
                                    oninput="document.getElementById('player_color').value = this.value">
                                <input type="text" name="player_color" id="player_color" class="form-control"
                                    value="{{ old('player_color', $settings['player_color'] ?? '#e50914') }}"
                                    placeholder="#e50914" maxlength="20"
                                    oninput="document.getElementById('player_color_picker').value = this.value">
                            </div>
                            @error('player_color')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="text-end">
            <button type="submit" id="submit-button" class="btn btn-primary">{{ __('messages.save') }}</button>
        </div>
    </form>
@endsection
