import assert from 'node:assert/strict'
import { readdirSync, readFileSync, statSync } from 'node:fs'
import { resolve } from 'node:path'
import { parse as parseScript } from '@babel/parser'
import { parse } from '@vue/compiler-sfc'

const root = resolve(import.meta.dirname, '..')
const pagesDir = resolve(root, 'src/pages')

function collectVueFiles(dir) {
  const entries = readdirSync(dir)
  const files = []

  for (const entry of entries) {
    const absolutePath = resolve(dir, entry)
    const stat = statSync(absolutePath)

    if (stat.isDirectory()) {
      files.push(...collectVueFiles(absolutePath))
    }
    else if (entry.endsWith('.vue')) {
      files.push(absolutePath)
    }
  }

  return files
}

const failures = []

for (const file of collectVueFiles(pagesDir)) {
  const source = readFileSync(file, 'utf8')
  const result = parse(source, { filename: file, pad: 'space' })

  if (result.errors.length > 0) {
    failures.push(`${file}: ${result.errors.map(error => error.message).join('; ')}`)
    continue
  }

  const scripts = [
    result.descriptor.script,
    result.descriptor.scriptSetup,
  ].filter(Boolean)

  for (const script of scripts) {
    try {
      parseScript(script.content, {
        sourceType: 'module',
        plugins: ['typescript'],
      })
    }
    catch (error) {
      failures.push(`${file}: ${error.message}`)
    }
  }
}

assert.deepEqual(failures, [])
console.log('page-sfc-parse-contract passed')
