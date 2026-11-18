{{-- Main text field, bound to the text shared across all tools. --}}
@props(['id', 'label' => __('ui.tool.input_label'), 'placeholder' => __('ui.tool.placeholder')])
<div>
    <label for="{{ $id }}" class="label">{{ $label }}</label>
    <textarea id="{{ $id }}" dir="auto" rows="4" x-autogrow spellcheck="false"
              class="field mt-2 p-4 text-lead leading-relaxed resize-none min-h-36"
              placeholder="{{ $placeholder }}"
              :value="$store.text.value" @input="$store.text.set($event.target.value)"></textarea>
</div>
