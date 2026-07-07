import assert from 'node:assert/strict'
import { selectWeixinBaseUrl } from './env-base-url.ts'

assert.equal(
  selectWeixinBaseUrl({
    defaultBaseUrl: 'https://api.example.com/',
    envVersion: 'develop',
    weixinDevelopBaseUrl: 'https://dev.example.com/',
    weixinTrialBaseUrl: 'https://trial.example.com/',
    weixinReleaseBaseUrl: 'https://release.example.com/',
  }),
  'https://dev.example.com/',
)

assert.equal(
  selectWeixinBaseUrl({
    defaultBaseUrl: 'https://api.example.com/',
    envVersion: 'trial',
    weixinDevelopBaseUrl: 'https://dev.example.com/',
    weixinTrialBaseUrl: 'https://trial.example.com/',
    weixinReleaseBaseUrl: 'https://release.example.com/',
  }),
  'https://trial.example.com/',
)

assert.equal(
  selectWeixinBaseUrl({
    defaultBaseUrl: 'https://api.example.com/',
    envVersion: 'release',
    weixinDevelopBaseUrl: 'https://dev.example.com/',
    weixinTrialBaseUrl: 'https://trial.example.com/',
    weixinReleaseBaseUrl: 'https://release.example.com/',
  }),
  'https://release.example.com/',
)

assert.equal(
  selectWeixinBaseUrl({
    defaultBaseUrl: 'https://api.example.com/',
    envVersion: 'release',
    weixinDevelopBaseUrl: '',
    weixinTrialBaseUrl: '',
    weixinReleaseBaseUrl: '',
  }),
  'https://api.example.com/',
)
