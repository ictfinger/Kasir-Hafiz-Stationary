// Barcode Generator using JsBarcode library
// This file provides utility functions for barcode generation

/**
 * Generate barcode SVG element
 * @param {string} elementId - ID of the SVG element
 * @param {string} value - Barcode value
 * @param {object} options - JsBarcode options
 */
function generateBarcode(elementId, value, options = {}) {
    const defaultOptions = {
        format: "EAN13",
        width: 2,
        height: 60,
        displayValue: true,
        fontSize: 14,
        margin: 10
    };
    
    const mergedOptions = { ...defaultOptions, ...options };
    
    try {
        JsBarcode(elementId, value, mergedOptions);
        return true;
    } catch (error) {
        console.error('Error generating barcode:', error);
        return false;
    }
}

/**
 * Download barcode as image
 * @param {string} elementId - ID of the SVG element
 * @param {string} filename - Name for the downloaded file
 */
function downloadBarcode(elementId, filename = 'barcode.png') {
    const svg = document.querySelector(elementId);
    if (!svg) return;
    
    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const data = new XMLSerializer().serializeToString(svg);
    const DOMURL = window.URL || window.webkitURL || window;
    
    const img = new Image();
    const svgBlob = new Blob([data], { type: 'image/svg+xml;charset=utf-8' });
    const url = DOMURL.createObjectURL(svgBlob);
    
    img.onload = function() {
        canvas.width = img.width;
        canvas.height = img.height;
        ctx.drawImage(img, 0, 0);
        DOMURL.revokeObjectURL(url);
        
        const imgURI = canvas.toDataURL('image/png');
        const evt = new MouseEvent('click', {
            view: window,
            bubbles: false,
            cancelable: true
        });
        
        const a = document.createElement('a');
        a.setAttribute('download', filename);
        a.setAttribute('href', imgURI);
        a.setAttribute('target', '_blank');
        a.dispatchEvent(evt);
    };
    
    img.src = url;
}

// Export functions (for module usage if needed)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        generateBarcode,
        downloadBarcode
    };
}
