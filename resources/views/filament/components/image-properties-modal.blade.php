<script src="{{ asset('js/image-properties-modal.js') }}"></script>

<div x-data="imagePropertiesModal()" x-init="init()" @keydown.escape.window="closeModal()">
    <!-- Modal Overlay -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <!-- Background overlay -->
        <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
             @click="closeModal()"></div>

        <!-- Modal content -->
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full p-6 dark:bg-gray-800"
                 @click.stop>

                <!-- Modal header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Image Properties
                    </h3>
                    <button @click="closeModal()"
                            type="button"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form fields -->
                <div class="space-y-4">
                    <!-- Caption -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Chú thích ảnh (Caption)
                        </label>
                        <input type="text"
                               x-model="modalData.caption"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="Nhập chú thích cho ảnh">
                    </div>

                    <!-- Alt text -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Văn bản thay thế (Alt)
                        </label>
                        <input type="text"
                               x-model="modalData.alt"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="Mô tả ngắn gọn về ảnh">
                    </div>

                    <!-- Width -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Chiều rộng (px)
                        </label>
                        <input type="text"
                               x-model="modalData.width"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="VD: 800, 800px, 100%, hoặc auto">
                    </div>

                    <!-- Height -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Chiều cao (px)
                        </label>
                        <input type="text"
                               x-model="modalData.height"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none dark:bg-gray-700 dark:border-gray-600 dark:text-white"
                               placeholder="VD: 600, 600px, auto">
                    </div>
                </div>

                <!-- Modal actions -->
                <div class="flex gap-3 mt-6">
                    <button @click="submitModal()"
                            type="button"
                            class="flex-1 px-4 py-2 text-white font-medium rounded-lg transition-colors" style="background-color: #39f;">
                        Đồng ý
                    </button>
                    <button @click="closeModal()"
                            type="button"
                            class="flex-1 px-4 py-2 text-gray-700 font-medium rounded-lg transition-colors" style="background-color: #e5e7eb;">
                        Bỏ qua
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
        [x-cloak] { display: none !important; }
    </style>
</div>

<script>
function imagePropertiesModal() {
    return {
        showModal: false,
        currentAttachment: null,
        currentEditor: null,
        currentImage: null,
        isEditingExisting: false,
        modalData: {
            caption: '',
            alt: '',
            width: '',
            height: ''
        },

        init() {
            const self = this;

            window.addEventListener('open-image-modal', function(event) {
                self.openModalForAttachment(event.detail.attachment, event.detail.editor);
            });

            window.addEventListener('edit-image-modal', function(event) {
                self.openModalForImage(event.detail.image);
            });
        },

        openModalForAttachment(attachment, editor) {
            this.currentAttachment = attachment;
            this.currentEditor = editor;
            this.currentImage = null;
            this.isEditingExisting = false;
            this.modalData = { caption: '', alt: '', width: '', height: '' };
            this.showModal = true;
        },

        openModalForImage(img) {
            this.currentImage = img;
            this.currentAttachment = null;
            this.isEditingExisting = true;

            const figure = img.closest('figure');
            const figcaption = figure ? figure.querySelector('figcaption') : null;

            this.modalData = {
                caption: figcaption ? figcaption.textContent : (img.dataset.caption || ''),
                alt: img.alt || '',
                width: img.style.width || img.getAttribute('width') || '',
                height: img.style.height || img.getAttribute('height') || ''
            };

            this.showModal = true;
        },

        submitModal() {
            if (this.isEditingExisting && this.currentImage) {
                this.updateExistingImage();
            } else if (this.currentAttachment) {
                this.insertNewImage();
            }

            // Clear attachment reference so closeModal() doesn't insert again
            this.currentAttachment = null;
            this.currentEditor = null;

            this.closeModal();
        },

        insertNewImage() {
            const attachment = this.currentAttachment;
            const url = attachment.getURL();

            let html = '<figure class="attachment">';
            html += '<img src="' + url + '"';

            if (this.modalData.alt) {
                html += ' alt="' + this.modalData.alt + '"';
            }
            if (this.modalData.caption) {
                html += ' data-caption="' + this.modalData.caption + '"';
            }

            if (this.modalData.width) {
                const width = /\d+(%|px|em|rem)?$/.test(this.modalData.width) ? this.modalData.width : this.modalData.width + 'px';
                html += ' style="width: ' + width + ';';

                if (this.modalData.height) {
                    const height = /\d+(%|px|em|rem)?$/.test(this.modalData.height) ? this.modalData.height : this.modalData.height + 'px';
                    html += ' height: ' + height + ';';
                }
                html += '"';
            } else if (this.modalData.height) {
                const height = /\d+(%|px|em|rem)?$/.test(this.modalData.height) ? this.modalData.height : this.modalData.height + 'px';
                html += ' style="height: ' + height + ';"';
            }

            html += '/>';

            if (this.modalData.caption) {
                html += '<figcaption class="attachment__caption">' + this.modalData.caption + '</figcaption>';
            }

            html += '</figure>';

            attachment.remove();
            this.currentEditor.insertHTML(html);
        },

        insertDefaultImage() {
            if (!this.currentAttachment || !this.currentEditor) return;

            const attachment = this.currentAttachment;
            const url = attachment.getURL();
            const html = '<figure class="attachment"><img src="' + url + '" /></figure>';

            attachment.remove();
            this.currentEditor.insertHTML(html);
        },

        updateExistingImage() {
            const img = this.currentImage;

            if (this.modalData.alt) {
                img.alt = this.modalData.alt;
            } else {
                img.removeAttribute('alt');
            }

            img.dataset.caption = this.modalData.caption || '';

            let figure = img.closest('figure');
            if (!figure) {
                figure = document.createElement('figure');
                figure.className = 'attachment';
                img.parentNode.insertBefore(figure, img);
                figure.appendChild(img);
            }

            let figcaption = figure.querySelector('figcaption');
            if (this.modalData.caption) {
                if (!figcaption) {
                    figcaption = document.createElement('figcaption');
                    figcaption.className = 'attachment__caption';
                    figure.appendChild(figcaption);
                }
                figcaption.textContent = this.modalData.caption;
            } else if (figcaption) {
                figcaption.remove();
            }

            let styles = [];
            if (this.modalData.width) {
                const width = /\d+(%|px|em|rem)?$/.test(this.modalData.width) ? this.modalData.width : this.modalData.width + 'px';
                styles.push('width: ' + width);
            }
            if (this.modalData.height) {
                const height = /\d+(%|px|em|rem)?$/.test(this.modalData.height) ? this.modalData.height : this.modalData.height + 'px';
                styles.push('height: ' + height);
            }

            if (styles.length > 0) {
                img.style.cssText = styles.join('; ');
            } else {
                img.style.cssText = '';
            }
        },

        closeModal() {
            if (!this.isEditingExisting && this.currentAttachment) {
                this.insertDefaultImage();
            }

            this.showModal = false;
            this.currentAttachment = null;
            this.currentEditor = null;
            this.currentImage = null;
            this.modalData = { caption: '', alt: '', width: '', height: '' };
        }
    };
}
</script>
