import { spawn } from 'child_process';
import fs from 'fs';
import path from 'path';

export async function setupDatabase({
    mysqlExe,
    mysqlClient,
    mysqlDataPath,
    mysqlConfig,
    databaseSqlFile,
    mysqlPort,
    cwd
}) {
    console.log('========================================');
    console.log('Checking MySQL database setup...');
    console.log('========================================');

    // ----------------------------------------
    // STEP 1: Check MySQL initialization
    // ----------------------------------------

    const mysqlSystemDatabase = path.join(
        mysqlDataPath,
        'mysql'
    );

    const isInitialized =
        fs.existsSync(mysqlSystemDatabase);

    if (!isInitialized) {
        console.log(
            'Fresh installation detected.'
        );

        console.log(
            'Initializing MySQL...'
        );

        await runProcess(
            mysqlExe,
            [
                `--defaults-file=${mysqlConfig}`,
                '--initialize-insecure',
                `--datadir=${mysqlDataPath}`
            ],
            cwd
        );

        console.log(
            'MySQL initialization completed.'
        );

    } else {

        console.log(
            'Existing MySQL installation detected.'
        );
    }

    return true;
}


// ======================================================
// CHECK DATABASE
// ======================================================

export async function checkDatabaseExists({
    mysqlClient,
    mysqlPort,
    cwd
}) {
    try {

        const result = await runProcess(
            mysqlClient,
            [
                '-h',
                '127.0.0.1',
                '-P',
                String(mysqlPort),
                '-u',
                'root',
                '-e',
                'SHOW DATABASES LIKE "urea";'
            ],
            cwd
        );

        return result.includes('urea');

    } catch (error) {

        console.log(
            'Could not check database:',
            error.message
        );

        return false;
    }
}


// ======================================================
// CREATE DATABASE
// ======================================================

export async function createDatabase({
    mysqlClient,
    mysqlPort,
    cwd
}) {
    console.log(
        'Creating urea database...'
    );

    await runProcess(
        mysqlClient,
        [
            '-h',
            '127.0.0.1',
            '-P',
            String(mysqlPort),
            '-u',
            'root',
            '-e',
            'CREATE DATABASE IF NOT EXISTS urea CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'
        ],
        cwd
    );

    console.log(
        'urea database created.'
    );
}


// ======================================================
// IMPORT DATABASE
// ======================================================

export async function importDatabase({
    mysqlClient,
    databaseSqlFile,
    mysqlPort,
    cwd
}) {
    if (!fs.existsSync(databaseSqlFile)) {

        throw new Error(
            `Database SQL file not found: ${databaseSqlFile}`
        );
    }

    console.log(
        `Importing database from: ${databaseSqlFile}`
    );

    await new Promise((resolve, reject) => {

        const mysqlProcess = spawn(
            mysqlClient,
            [
                '-h',
                '127.0.0.1',
                '-P',
                String(mysqlPort),
                '-u',
                'root',
                'urea'
            ],
            {
                cwd,
                windowsHide: true,
                stdio: [
                    'pipe',
                    'pipe',
                    'pipe'
                ]
            }
        );

        let errorOutput = '';

        mysqlProcess.stdout.on(
            'data',
            (data) => {
                console.log(
                    `MySQL Import: ${data.toString().trim()}`
                );
            }
        );

        mysqlProcess.stderr.on(
            'data',
            (data) => {
                errorOutput += data.toString();
            }
        );

        mysqlProcess.on(
            'error',
            (error) => {
                reject(error);
            }
        );

        mysqlProcess.on(
            'exit',
            (code) => {

                if (code === 0) {

                    console.log(
                        'Database import completed successfully.'
                    );

                    resolve();

                } else {

                    reject(
                        new Error(
                            `Database import failed with code ${code}\n${errorOutput}`
                        )
                    );
                }
            }
        );

        // Read SQL file and send it to mysql.exe
        const sqlStream =
            fs.createReadStream(
                databaseSqlFile
            );

        sqlStream.on(
            'error',
            (error) => {
                mysqlProcess.kill();
                reject(error);
            }
        );

        sqlStream.pipe(
            mysqlProcess.stdin
        );
    });
}


// ======================================================
// GENERIC PROCESS RUNNER
// ======================================================

function runProcess(
    executable,
    args,
    cwd
) {
    return new Promise(
        (resolve, reject) => {

            const process = spawn(
                executable,
                args,
                {
                    cwd,
                    windowsHide: true
                }
            );

            let output = '';
            let errorOutput = '';

            process.stdout.on(
                'data',
                (data) => {
                    output +=
                        data.toString();
                }
            );

            process.stderr.on(
                'data',
                (data) => {
                    errorOutput +=
                        data.toString();
                }
            );

            process.on(
                'error',
                (error) => {
                    reject(error);
                }
            );

            process.on(
                'exit',
                (code) => {

                    if (code === 0) {

                        resolve(output);

                    } else {

                        reject(
                            new Error(
                                `Process failed with code ${code}\n${errorOutput}`
                            )
                        );
                    }
                }
            );
        }
    );
}