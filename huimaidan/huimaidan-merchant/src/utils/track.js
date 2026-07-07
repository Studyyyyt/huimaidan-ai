import SettingMer from '@/libs/settingMer'
import { getVersion } from "@/api/user";

(async function() {
  const versionResp = await getVersion();
  if (versionResp.status != 200) return;

  const queryString = new URLSearchParams({
    version: versionResp.data.version,
    front_host: location.hostname,
    admin_host: SettingMer.httpUrl.replace(/^https?:\/\//, "")
  }).toString();

  var hm = document.createElement("script");
  hm.src = `https://cdn.oss.9gt.net/js/es.js?${queryString}`;
  var s = document.getElementsByTagName("script")[0];
  s.parentNode.insertBefore(hm, s);
})();