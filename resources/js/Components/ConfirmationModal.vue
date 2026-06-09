<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
            <div class="flex items-start">

                <div class="ml-4 w-full">
                    <h3 
                        class="text-lg font-medium text-gray-900"
                        id="modal-title"
                    >
                        <slot name="title" />
                    </h3>

                    <div 
                        class="mt-2 text-sm text-gray-600"
                        id="modal-description"
                    >
                        <slot name="content" />
                    </div>
                </div>
            </div>
        </div>

        <div 
            class="px-6 py-4 bg-gray-100 flex justify-end gap-3"
            v-if="$slots.footer"
        >
            <slot name="footer" />
        </div>
    </Modal>
</template>