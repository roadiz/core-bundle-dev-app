const xxl = 1920
const xl = 1450
const lg = 1280
const md = 960
const sm = 768
const xs = 480

// For now keep same naming as app/assets/less/vars.less variables
// TODO: rename less variables (@screen-xxl-min) to postcss-simple-vars $screen-xxl-min
const medias = {
    'screen-xxl-min': xxl + 'px',
    'screen-xl-max': xxl - 1 + 'px',
    'screen-xl-min': xl + 'px',
    'screen-lg-max': xl - 1 + 'px',
    'screen-lg-min': lg + 'px',
    'screen-md-max': lg - 1 + 'px',
    'screen-md-min': md + 'px',
    'screen-sm-max': md - 1 + 'px',
    'screen-sm-min': sm + 'px',
    'screen-xs-max': sm - 1 + 'px',
    'screen-xs-min': xs + 'px',
}

export default medias
