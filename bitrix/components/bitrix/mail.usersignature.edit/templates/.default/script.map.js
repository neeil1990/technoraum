{"version":3,"sources":["script.js"],"names":["BX","namespace","Mail","UserSignature","Edit","signatureId","init","params","this","singleselect","input","options","findChildren","tag","attr","type","i","bind","checked","value","input1","getAttribute","label0","findNextSibling","for","id","label1","adjust","text","innerHTML","setAttribute","event","window","skip_singleselect","document","selectInputs","findChildrenByClassName","save","closeAfter","signature","sender","list","senders","hasOwnProperty","ajax","runAction","data","userSignatureId","fields","then","response","closeSlider","UI","Notification","Center","notify","content","message","showError","errors","pop","userSignature","alert","Alert","color","Color","DANGER","icon","Icon","html","append","getContainer","SidePanel","slider","Instance","getTopSlider","postMessage","fireEvent","showList","show","hide"],"mappings":"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","file":""}