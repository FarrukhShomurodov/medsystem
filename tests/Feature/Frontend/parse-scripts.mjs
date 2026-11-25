// Reads [{label, code, module}] as JSON on stdin and prints the scripts that do not parse as
// [{label, error}] (an empty list when all of them do). Only parses: nothing is executed.
import vm from 'node:vm';

let input = '';
process.stdin.setEncoding('utf8');
process.stdin.on('data', (chunk) => (input += chunk));
process.stdin.on('end', () => {
    const failures = [];

    for (const { label, code, module } of JSON.parse(input)) {
        try {
            if (module && typeof vm.SourceTextModule === 'function') {
                new vm.SourceTextModule(code);
            } else if (!module) {
                new vm.Script(code, { filename: label });
            }
        } catch (error) {
            failures.push({ label, error: String(error.message ?? error) });
        }
    }

    process.stdout.write(JSON.stringify(failures));
});
