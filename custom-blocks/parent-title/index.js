const { useBlockProps } = wp.blockEditor;

wp.blocks.registerBlockType('smn/parent-title', {
    apiVersion: 2,
    title: 'Título del padre',
    icon: 'heading',
    category: 'theme',
    edit: function() {
        const blockProps = useBlockProps({ className: 'wp-block-parent-title' });
        return wp.element.createElement(
            'div',
            blockProps,
            'Título de la página padre'
        );
    },
    save: function() {
        return null; // Bloque dinámico, renderizado por PHP
    }
});