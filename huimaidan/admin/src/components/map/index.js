let task = null;

export function loadMapLib(key) {
  if (task) return task;
  task = new Promise((resolve, reject) => {
    if (window.TMap) {
      return resolve();
    }
    window.initMap = () => {
      resolve();
    };
    const script = document.createElement("script");
    script.type = "text/javascript";
    script.src = `https://map.qq.com/api/gljs?libraries=tools&v=1.exp&key=${key}&callback=initMap`;
    script.onerror = reject;
    document.body.appendChild(script);
  });

  return task;
}
