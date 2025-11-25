// Image Properties Modal for Filament Trix Editor
(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initImagePropertiesModal);
    } else {
        initImagePropertiesModal();
    }

    function initImagePropertiesModal() {
        console.log('Image Properties Modal: Initializing...');

        // Listen to Trix attachment events
        document.addEventListener('trix-attachment-add', function(event) {
            const attachment = event.attachment;
            if (attachment.file && attachment.file.type.startsWith('image/')) {
                console.log('Image Properties Modal: Image attachment detected');
                // Wait for upload to complete
                const checkUpload = setInterval(function() {
                    if (attachment.getURL()) {
                        clearInterval(checkUpload);
                        console.log('Image Properties Modal: Upload complete, showing modal');
                        window.dispatchEvent(new CustomEvent('open-image-modal', {
                            detail: {
                                attachment: attachment,      // The Trix attachment object
                                editor: event.target.editor // The Trix editor instance
                            }
                        }));
                    }
                }, 100);
            }
        });

        // Listen to image clicks in Trix editor
        document.addEventListener('click', function(event) {
            const img = event.target.closest('.fi-fo-rich-editor img');
            if (img) {
                event.preventDefault();
                console.log('Image Properties Modal: Image clicked');
                window.dispatchEvent(new CustomEvent('edit-image-modal', {
                    detail: { image: img }
                }));
            }
        });

        console.log('Image Properties Modal: Event listeners attached');
    }
})();
