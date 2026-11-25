const {chromium}=require('C:/Users/Orange/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const {pathToFileURL}=require('url');
const path=require('path');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
 const page=await browser.newPage();
 await page.goto(pathToFileURL(path.join(__dirname,'Omar-YallaTeer-Contributions.html')).href);
 await page.evaluate(()=>document.fonts.ready);
 await page.pdf({path:path.join(__dirname,'Omar-YallaTeer-Contributions.pdf'),format:'A4',printBackground:true,preferCSSPageSize:true,displayHeaderFooter:true,headerTemplate:'<span></span>',footerTemplate:'<div style="font-family:Arial;font-size:9px;width:100%;padding:0 55px;color:#70808b;display:flex;justify-content:space-between"><span>YALLA TEER | OMAR AL-TOOM</span><span><span class="pageNumber"></span> / <span class="totalPages"></span></span></div>'});
 await browser.close();
 console.log('PDF created');
})();
