let fs = require('fs')
let brotliSize = require('brotli-size')
let crypto = require('crypto')


let hash = crypto.randomBytes(4).toString('hex');

if (!fs.existsSync(__dirname+'/dist')){
    fs.mkdirSync(__dirname+'/dist');
}

fs.writeFileSync(__dirname+'/dist/manifest.json', `
{"/wire-spy.min.js":"${hash}"}
`)

// Build a minified version.
build({
    entryPoints: [`resources/js/wire-spy.js`],
    outfile: `dist/wire-spy.min.js`,
    sourcemap: 'linked',
    bundle: true,
    minify: true,
    platform: 'browser',
}).then(() => {
    outputSize(`dist/wire-spy.min.js`)
})

function build(options) {
    options.define || (options.define = {})

    return require('esbuild').build({
        ...options,
    }).catch(() => process.exit(1))
}
function outputSize(file) {
    let size = bytesToSize(brotliSize.sync(fs.readFileSync(file)))

    console.log("\x1b[32m", `Bundle size: ${size}`)
}

function bytesToSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
    if (bytes === 0) return 'n/a'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10)
    if (i === 0) return `${bytes} ${sizes[i]}`
    return `${(bytes / (1024 ** i)).toFixed(1)} ${sizes[i]}`
}