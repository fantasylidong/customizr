module.exports = {
  czr_control_js : {
    // Target-specific file lists and/or options go here.
    options: {
        singleline: true,
        multiline: false
    },
    src: [ '<%= paths.czr_assets %>js/czr-control.js'] // files to remove comments from
  }
};