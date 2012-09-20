$.extend($.expr[':'], {
  'containsText': function(elem, i, match, array) {
    return (elem.textContent || elem.innerText || '').toLowerCase()
    .indexOf((match[3] || "").toLowerCase()) >= 0;
  }
});
jQuery.extend(jQuery.expr[':'], {
	exactIgnoreCase: "(a.textContent||a.innerText||jQuery(a).text()||'').toLowerCase() == (m[3]).toLowerCase()"
});
