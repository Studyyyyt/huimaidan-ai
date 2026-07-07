export interface IInviteScene {
  type: 'invite'
  spreadUid: number
}

export interface IStoreQrcodeScene {
  type?: 'store'
  merId: number
  entryCode: string
}

export type IScanScene = IInviteScene | Required<IStoreQrcodeScene>

function decodeScene(rawScene: string | undefined) {
  let scene = ''
  try {
    scene = decodeURIComponent(rawScene || '')
  }
  catch {
    throw new Error('二维码参数错误')
  }

  return scene
}

export function parseScanScene(rawScene: string | undefined): IScanScene {
  const scene = decodeScene(rawScene)

  const inviteMatched = /^i([1-9]\d*)$/.exec(scene)
  if (inviteMatched) {
    return { type: 'invite', spreadUid: Number(inviteMatched[1]) }
  }

  const matched = /^m([1-9]\d*)\.e([A-Za-z0-9]{6,10})$/.exec(scene)
  if (!matched) {
    throw new Error('二维码参数错误')
  }

  return {
    type: 'store',
    merId: Number(matched[1]),
    entryCode: matched[2],
  }
}

export function parseStoreQrcodeScene(rawScene: string | undefined) {
  const scene = parseScanScene(rawScene)
  if (scene.type !== 'store') {
    throw new Error('二维码参数错误')
  }

  return {
    merId: scene.merId,
    entryCode: scene.entryCode,
  }
}

export function buildStoreQrcodeCheckoutUrl(scene: IStoreQrcodeScene) {
  return `/pages/payment/checkout?id=${scene.merId}&source=store_qrcode&entry_code=${scene.entryCode}`
}

export function buildInviteEntryUrl(scene: IInviteScene) {
  return `/pages/index/index?spread=${scene.spreadUid}`
}
