<div class="grid gap-4">

    {{-- INTRO --}}
    @if($state === 'intro')
        <div class="rounded-3xl bg-white border p-6">
            <div class="text-2xl sm:text-3xl font-extrabold">¡Vamos a practicar! 🎧</div>
            <div class="text-slate-600 mt-2 text-base sm:text-lg">
                Es fácil: mira, escucha y toca.
            </div>

            <div class="mt-6">
                <button wire:click="start"
                        class="w-full sm:w-auto rounded-3xl px-8 py-4 bg-slate-900 text-white font-extrabold text-lg">
                    Empezar 🚀
                </button>
            </div>
        </div>
    @endif


    {{-- FLASHCARDS --}}
    @if($state === 'flashcards')
        @php $c = $flashcards[$flashIndex] ?? null; @endphp

        <div class="rounded-3xl bg-white border p-6"
             x-data="{
                play(){
                    const el = this.$refs.audioEl;
                    if(!el) return;
                    el.currentTime = 0;
                    el.play().catch(()=>{});
                }
             }">

            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    Aprende ({{ $flashIndex+1 }}/{{ count($flashcards) }})
                </div>

                <button type="button" wire:click="exitSession"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                    Salir ✖
                </button>
            </div>

            <div class="mt-5">
                @if(!empty($c['image_path']))
                    <img src="{{ asset('storage/'.$c['image_path']) }}"
                         alt="{{ $c['word_en'] ?? '' }}"
                         class="w-full max-h-72 object-contain rounded-3xl bg-slate-50 border" />
                @else
                    <div class="w-full h-56 rounded-3xl bg-slate-50 border flex items-center justify-center text-slate-500 text-lg">
                        (sin imagen)
                    </div>
                @endif
            </div>

            <div class="mt-5 text-center">
                <div class="text-4xl sm:text-5xl font-extrabold">{{ $c['word_en'] ?? '' }}</div>
                <div class="text-slate-600 mt-2 text-xl sm:text-2xl font-semibold">{{ $c['translation_es'] ?? '' }}</div>
            </div>

            <div class="mt-6 flex justify-center">
                <button type="button" @click="play()"
                        class="rounded-3xl px-8 py-4 bg-white border font-extrabold text-lg hover:bg-slate-50">
                    🔊 Escuchar
                </button>
            </div>

            @if(!empty($c['audio_path']))
                <audio x-ref="audioEl">
                    <source src="{{ asset('storage/'.$c['audio_path']) }}" type="audio/mpeg">
                </audio>
            @endif

            <div class="mt-8 flex justify-end">
                <button wire:click="nextFlashcard"
                        class="rounded-3xl px-8 py-4 bg-slate-900 text-white font-extrabold text-lg">
                    Siguiente ▶
                </button>
            </div>
        </div>
    @endif


    {{-- LISTENING --}}
    @if($state === 'listening')
        @php
            $item = $listenItems[$listenIndex] ?? null;
            $target = $item['target'] ?? null;
            $options = $item['options'] ?? [];
        @endphp

        <div class="rounded-3xl bg-white border p-6"
             x-data="{
                play(){
                    const el = this.$refs.audioEl;
                    if(!el) return;
                    el.currentTime = 0;
                    el.play().catch(()=>{});
                },
                revealCorrect(correctId){
                    document.querySelectorAll('[data-opt-id]').forEach(btn => {
                        if(parseInt(btn.dataset.optId) === parseInt(correctId)){
                            btn.classList.add('ring-4','ring-slate-900');
                        }
                    });
                }
             }"
             x-init="$nextTick(() => play())"
             x-on:listen:play-audio.window="play()"
             x-on:listen:reveal-correct.window="revealCorrect({{ (int)($target['id'] ?? 0) }})">

            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    Escucha y elige ({{ $listenIndex+1 }}/{{ count($listenItems) }})
                </div>

                <button type="button" wire:click="exitSession"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                    Salir ✖
                </button>
            </div>

            <div class="mt-3 flex items-center justify-between">
                <div class="text-xl sm:text-2xl font-extrabold">🔊 Escucha</div>

                <button type="button" @click="play()"
                        class="rounded-2xl px-4 py-2 bg-white border font-bold hover:bg-slate-50">
                    Repetir
                </button>
            </div>

            @if(!empty($target['audio_path']))
                <audio x-ref="audioEl">
                    <source src="{{ asset('storage/'.$target['audio_path']) }}" type="audio/mpeg">
                </audio>
            @endif

            {{-- feedback --}}
            @if($lastFeedback === 'wrong')
                <div class="mt-4 rounded-2xl border bg-white p-4 text-slate-700 font-semibold">
                    Ups… intenta otra vez 🙂
                    <div class="text-sm text-slate-500 mt-1">Intento {{ $listenAttemptNo-1 }} de 3</div>
                </div>
            @elseif($lastFeedback === 'correct')
                <div class="mt-4 rounded-2xl border bg-white p-4 text-slate-700 font-semibold">
                    ¡Correcto! ✅
                </div>
            @endif

            {{-- opciones --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-5">
                @foreach($options as $i => $opt)
                    @php
                        $hidden = in_array((int)($opt['id'] ?? 0), array_map('intval', $listenHidden), true);
                    @endphp

                    <button
                        wire:click="pickListenOption({{ $i }})"
                        wire:loading.attr="disabled"
                        data-opt-id="{{ (int)($opt['id'] ?? 0) }}"
                        class="rounded-3xl border p-5 sm:p-6 text-left hover:bg-slate-50 disabled:opacity-60 {{ $hidden ? 'hidden' : '' }}"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-14 w-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-extrabold">
                                {{ $i+1 }}
                            </div>

                            <div class="flex-1">
                                <div class="text-lg sm:text-xl font-extrabold">{{ $opt['word_en'] ?? '' }}</div>
                                <div class="text-slate-600 font-semibold">{{ $opt['translation_es'] ?? '' }}</div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 text-sm text-slate-500">
                Pistas usadas: {{ $listenHintsUsed }}
            </div>
        </div>
    @endif


    {{-- MATCHING (placeholder; NO lo borramos) --}}
    @if($state === 'matching')
        <div class="rounded-3xl bg-white border p-6">
            <div class="text-2xl font-extrabold">Emparejar 🧩</div>
            <div class="text-slate-600 mt-2">
                (Por ahora placeholder). Luego lo mejoramos con “imagen vs imagen”.
            </div>

            <div class="mt-6 flex justify-end">
                <button wire:click="nextMatching"
                        class="rounded-3xl px-8 py-4 bg-slate-900 text-white font-extrabold text-lg">
                    Siguiente ▶
                </button>
            </div>
        </div>
    @endif


    {{-- MULTIPLE CHOICE --}}
    @if($state === 'multiple_choice')
        @php
            $q = $quizQuestions[$quizIndex] ?? null;
            $opts = $q['options'] ?? [];
        @endphp

        <div class="rounded-3xl bg-white border p-6"
             x-data="{
                highlight(correctId){
                    document.querySelectorAll('[data-mc-id]').forEach(btn => {
                        if(parseInt(btn.dataset.mcId) === parseInt(correctId)){
                            btn.classList.add('ring-4','ring-slate-900');
                        }
                    });
                }
             }"
             x-on:quiz:highlight-correct.window="highlight($event.detail.correctId)"
             x-on:quiz:reveal-correct.window="highlight($event.detail.correctId)">

            <div class="flex items-center justify-between">
                <div class="text-sm text-slate-500">
                    Mini-quiz ({{ $quizIndex+1 }}/{{ max(1, count($quizQuestions)) }})
                </div>

                <button type="button" wire:click="exitSession"
                        class="text-sm font-semibold text-slate-600 hover:text-slate-900">
                    Salir ✖
                </button>
            </div>

            <div class="mt-4 text-2xl sm:text-3xl font-extrabold">
                {{ $q['prompt'] ?? 'Elige la respuesta correcta' }}
            </div>

            {{-- feedback --}}
            @if($quizFeedback === 'wrong')
                <div class="mt-4 rounded-2xl border bg-white p-4 text-slate-700 font-semibold">
                    Ups… intenta otra vez 🙂
                    <div class="text-sm text-slate-500 mt-1">Intento {{ $quizAttemptNo-1 }} de 3</div>
                </div>
            @elseif($quizFeedback === 'correct')
                <div class="mt-4 rounded-2xl border bg-white p-4 text-slate-700 font-semibold">
                    ¡Correcto! ✅
                </div>
            @endif

            <div class="grid grid-cols-1 gap-3 mt-5">
                @foreach($opts as $i => $opt)
                    <button
                        wire:click="pickQuizOption({{ $i }})"
                        wire:loading.attr="disabled"
                        data-mc-id="{{ (int)($opt['id'] ?? 0) }}"
                        class="rounded-3xl border p-5 text-left hover:bg-slate-50 disabled:opacity-60"
                    >
                        <div class="flex items-center gap-4">
                            <div class="h-12 w-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-extrabold">
                                {{ $i+1 }}
                            </div>

                            <div class="flex-1">
                                <div class="text-lg sm:text-xl font-extrabold">
                                    {{ $opt['text'] ?? '' }}
                                </div>
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            <div class="mt-4 text-sm text-slate-500">
                Pistas usadas: {{ $quizHintsUsed }}
            </div>
        </div>
    @endif


    {{-- SUMMARY --}}
    @if($state === 'summary')
        <div class="rounded-3xl bg-white border p-6">
            <div class="text-2xl sm:text-3xl font-extrabold">¡Excelente! ⭐</div>
            <div class="text-slate-600 mt-2 text-base sm:text-lg">
                Terminaste la sesión.
            </div>

            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                <a href="{{ route('student.lessons.index') }}"
                   class="inline-flex items-center justify-center rounded-3xl px-8 py-4 bg-slate-900 text-white font-extrabold text-lg">
                    Volver a mis lecciones
                </a>

                <button wire:click="$set('state','intro')"
                        class="inline-flex items-center justify-center rounded-3xl px-8 py-4 bg-white border font-extrabold text-lg">
                    Repetir 🔁
                </button>
            </div>
        </div>
    @endif

</div>
