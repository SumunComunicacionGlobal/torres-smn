(function () {
    function isAccordionContentTag(tag) {
        return tag === 'p' || tag === 'ul' || tag === 'ol' || tag === 'h3' || tag === 'h4' || tag === 'h5' || tag === 'h6';
    }

    function isAccordionEligibleStart(node) {
        if (!node || node.nodeType !== Node.ELEMENT_NODE) {
            return false;
        }

        var tag = node.tagName.toLowerCase();
        return isAccordionContentTag(tag);
    }

    function isAccordionContentNode(node) {
        if (!node || node.nodeType !== Node.ELEMENT_NODE) {
            return false;
        }

        var tag = node.tagName.toLowerCase();
        return isAccordionContentTag(tag);
    }

    function buildAccordionFromHeadings(container) {
        var childNodes = Array.from(container.children);

        childNodes.forEach(function (node) {
            if (!node || node.tagName !== 'H2') {
                return;
            }

            // Skip headings that were already transformed by a previous run.
            if (node.closest('details.equipo-accordion__item')) {
                return;
            }

            var contentNodes = [];
            var cursor = node.nextElementSibling;

            while (cursor && cursor.tagName !== 'H2' && isAccordionContentNode(cursor)) {
                contentNodes.push(cursor);
                cursor = cursor.nextElementSibling;
            }

            if (!contentNodes.length) {
                return;
            }

            if (!isAccordionEligibleStart(contentNodes[0])) {
                return;
            }

            var details = document.createElement('details');
            details.className = 'equipo-accordion__item';

            var summary = document.createElement('summary');
            summary.className = 'equipo-accordion__summary';

            var title = document.createElement('span');
            title.className = 'equipo-accordion__title';
            title.innerHTML = node.innerHTML;

            summary.appendChild(title);
            details.appendChild(summary);

            var content = document.createElement('div');
            content.className = 'equipo-accordion__content';

            contentNodes.forEach(function (contentNode) {
                content.appendChild(contentNode);
            });

            details.appendChild(content);
            node.replaceWith(details);
        });
    }

    function initEquipoAccordion() {
        var container = document.querySelector('.wp-block-post-content, .entry-content');
        if (!container) {
            return;
        }

        buildAccordionFromHeadings(container);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEquipoAccordion);
    } else {
        initEquipoAccordion();
    }
})();
