import { afterEach, expect, test } from 'vitest';
import { mount } from '@vue/test-utils';
import Textarea from '@/components/ui/Textarea.vue';
import '../../../css/app.css';

let wrapper;
afterEach(() => wrapper?.unmount());

const lines = Array.from({ length: 20 }, (_, i) => `Line ${i + 1}`).join('\n');

function render(modelValue) {
    wrapper = mount(Textarea, { attachTo: document.body, props: { elastic: true, modelValue } });
    return wrapper.find('textarea').element;
}

test('an elastic textarea grows when its value is changed programmatically', async () => {
    const textarea = render('');
    const initialHeight = textarea.offsetHeight;

    await wrapper.setProps({ modelValue: lines });

    expect(textarea.offsetHeight).toBeGreaterThan(initialHeight);
    expect(textarea.scrollHeight).toBeLessThanOrEqual(textarea.clientHeight);
});

test('an elastic textarea shrinks when its value is cleared programmatically', async () => {
    const textarea = render(lines);
    const initialHeight = textarea.offsetHeight;

    await wrapper.setProps({ modelValue: '' });

    expect(textarea.offsetHeight).toBeLessThan(initialHeight);
});
