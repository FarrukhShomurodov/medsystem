// After `npm run build`: every entry a view loads (@vite(...), <x-optional-vite :entrypoints>) is an
// entry of public/build/manifest.json, and every Vite::asset(...) file is in it. A view asking for a
// file the build does not have throws "Unable to locate file in Vite manifest" in production.
//
//   node tests/Feature/Frontend/check-manifest.mjs        (CI job "assets"; exit code 1 on a problem)
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';

const views = 'resources/views';
const manifest = JSON.parse(readFileSync('public/build/manifest.json', 'utf8'));

function* bladeFiles(directory) {
    for (const name of readdirSync(directory)) {
        const path = join(directory, name);

        if (statSync(path).isDirectory()) {
            yield* bladeFiles(path);
        } else if (name.endsWith('.blade.php')) {
            yield path;
        }
    }
}

const quoted = (text) => [...text.matchAll(/['"]([^'"]+)['"]/g)].map((match) => match[1]);
const problems = [];
let checked = 0;

for (const file of bladeFiles(views)) {
    const view = relative(views, file).replaceAll('\\', '/');
    const source = readFileSync(file, 'utf8').replace(/\{\{--[\s\S]*?--\}\}/g, '');

    const entries = [
        ...[...source.matchAll(/@vite\s*\(([\s\S]*?)\)/g)].flatMap((match) => quoted(match[1])),
        ...[...source.matchAll(/<x-optional-vite\b[^>]*:entrypoints="([\s\S]*?)"/g)].flatMap((match) => quoted(match[1])),
    ];
    const assets = [...source.matchAll(/Vite::asset\(\s*['"]([^'"]+)['"]\s*\)/g)].map((match) => match[1]);

    for (const entry of entries) {
        checked++;

        if (!manifest[entry]?.isEntry) {
            problems.push(`${view}: ${entry} is not an entry of the build`);
        }
    }

    for (const asset of assets) {
        checked++;

        if (!manifest[asset]?.file) {
            problems.push(`${view}: ${asset} is not in the build`);
        }
    }
}

if (problems.length > 0 || checked === 0) {
    console.error(problems.length > 0 ? problems.join('\n') : `No @vite reference found under ${views}.`);
    process.exit(1);
}

console.log(`public/build/manifest.json has all ${checked} files the views load.`);
