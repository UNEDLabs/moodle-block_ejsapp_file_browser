/**
 * Shows/hides the div section with the capture interaction options.    .
 *
 */
var showBut = document.getElementById('show');
showBut.onclick = function() {
    var divelement = document.getElementById('captureInteraction');
    divelement.style.display = 'block';
};

var hideBut = document.getElementById('hide');
hideBut.onclick = function() {
    var divelement = document.getElementById('captureInteraction');
    divelement.style.display = 'none';
};