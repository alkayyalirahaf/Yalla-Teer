from pathlib import Path
from html import escape
import re

out = Path(__file__).parent
source = (out.parent / 'omar-files.txt').read_text(encoding='utf-8-sig')
existing_block = source.split('=== EXISTING PROJECT FILES ===')[1].split('=== REMOVED OR RENAMED HISTORICAL PROJECT PATHS ===')[0]
old_block = source.split('=== REMOVED OR RENAMED HISTORICAL PROJECT PATHS ===')[1].split('=== DEPENDENCY PATHS')[0]
existing = [s.strip() for s in existing_block.splitlines() if s.strip() and not s.startswith('[')]
old = [s.strip() for s in old_block.splitlines() if s.strip()]
commits = [s.strip() for s in source.split('=== SOURCE COMMITS ===')[1].splitlines() if s.strip()]
assert len(existing) == 137 and len(old) == 39

css = '''
@page {size:A4;margin:16mm 15mm 18mm}
*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;color:#172b3b;font-size:12px;line-height:1.65}
.page{break-after:page;position:relative}.page:last-child{break-after:auto}
.eyebrow{font-size:10px;letter-spacing:2px;color:#866222;font-weight:bold;text-transform:uppercase}
h1{font-size:37px;line-height:1.5;margin:18px 0;color:#132b3e}h2{font-size:23px;margin:0 0 14px;border-bottom:2px solid #caa45b;padding-bottom:9px}h3{font-size:15px;margin:16px 0 7px;color:#866222}
p{margin:8px 0}.muted{color:#63717c}.cover{min-height:245mm;padding-top:20mm;border-top:7px solid #caa45b}
.name{font-size:23px;direction:ltr;text-align:right;font-weight:bold}.subtitle{font-size:18px}.summary{padding:18px;background:#f2f5f7;border-right:4px solid #caa45b;margin:20px 0}
.stats{display:flex;gap:12px;margin:24px 0}.stat{flex:1;border:1px solid #dbe2e6;padding:16px;text-align:center;border-radius:7px}.num{font-size:32px;color:#866222;font-weight:bold;line-height:1.3}
table{border-collapse:collapse;width:100%;font-size:12px}th{background:#183347;color:white;text-align:right;padding:9px}td{padding:8px 10px;border-bottom:1px solid #dfe5e8;vertical-align:top}tr:nth-child(even)td{background:#f7f9fa}
.code{direction:ltr;unicode-bidi:isolate;font-family:Consolas,'Courier New',monospace}.file{display:flex;direction:ltr;text-align:left;align-items:flex-start;gap:9px;padding:5px 7px;border-bottom:1px solid #e5eaed;font:10px/1.45 Consolas,'Courier New',monospace;overflow-wrap:anywhere;break-inside:avoid}.file:nth-child(even){background:#f5f7f8}.idx{color:#85919a;width:24px;flex-shrink:0}.path{flex:1;min-width:0;overflow-wrap:anywhere}
.note{font-size:11px;color:#63717c;padding:10px 12px;background:#f2f5f7;margin:12px 0}.label{font-size:11px;color:#63717c;margin-bottom:12px}.commit{direction:ltr;text-align:left;font:10px/1.7 Consolas,'Courier New',monospace;padding:7px 0;border-bottom:1px solid #e0e6e9;overflow-wrap:anywhere;break-inside:avoid}.refs{font:10px/1.8 Consolas,monospace;direction:ltr;text-align:left;overflow-wrap:anywhere}
'''
pages=[]
pages.append('''<section class="page cover"><div class="eyebrow">YALLA TEER · CONTRIBUTION REPORT</div><h1>تقرير مساهمات المشروع</h1><div class="name">Omar Al-Toom | عمر الطوم</div><p class="subtitle">الجزئية، الصفحات، والملفات التي جرى العمل عليها</p><div class="summary"><strong>Dev 3</strong><p>اكتشاف الرحلات، المساعد الذكي، وإدارة أرصدة العملاء، مع صفحات الإدارة والمساعدة المرتبطة بهذه الجزئية.</p></div><div class="stats"><div class="stat"><div class="num">137</div>ملف موجود حالياً</div><div class="stat"><div class="num">39</div>مسار قديم</div><div class="stat"><div class="num">176</div>مساراً في السجل</div></div><p class="muted">تاريخ إعداد التقرير: 23 نوفمبر 2026</p><p>يجمع هذا التقرير نطاق المهام الموثّق، الصفحات المرتبطة بالتنفيذ، والقائمة الكاملة للمسارات المسجّلة ضمن مساهمات عمر في Git.</p><div class="note">هذه قائمة مساهمات وليست إثبات ملكية حصرية. بعض الملفات مشتركة مع الفريق، وظهورها يعني أن أحد commits عمر أضافها أو عدّلها أو حذفها أو غيّر مسارها. لا يعني ذلك أنه كتب محتواها الحالي كاملاً.</div></section>''')
rows=[('B2','نتائج البحث عن الرحلات','الفلاتر، الفرز، الترقيم، وتوفّر الرحلات والمقاعد.'),('B3','تفاصيل الرحلة','عرض بيانات الرحلة والدرجات والأمتعة والأسعار.'),('B4','مقارنة الرحلات','مقارنة رحلتين أو ثلاث والتحقق من الاختيارات.'),('إضافية','ملخص اختيار الرحلات','حفظ وعرض اختيار الذهاب والعودة وتسليمه لخطوة الحجز.'),('B14','كتابة تقييم','التحقق من ملكية الحجز واكتمال الرحلة ومنع التكرار.'),('B16','المساعد الذكي','المساعد وسجل المحادثات وتفاصيل المحادثة.'),('A5a','إدارة الدول','القائمة والإضافة والتعديل والحذف والتحقق من العلاقات.'),('A9','إدارة أرصدة العملاء','زيادة وخصم وتسوية الرصيد وتوثيق العمليات.'),('A1','لوحة معلومات السوبر أدمن','عرض ملخصات وإحصاءات المنصة.'),('D2','الأسئلة الشائعة','صفحة FAQ.'),('D3','الشروط والأحكام','صفحة Terms.')]
table=''.join(f'<tr><td class="code">{escape(a)}</td><td>{b}</td><td>{c}</td></tr>' for a,b,c in rows)
pages.append('<section class="page"><h2>جزئيتك والصفحات المرتبطة بها</h2><table><thead><tr><th>الكود</th><th>الصفحة</th><th>طبيعة العمل</th></tr></thead><tbody>'+table+'''</tbody></table><h3>الخدمات والربط الخلفي</h3><p>خدمة البحث عن الرحلات، اختيار الذهاب والعودة وحفظ الاختيار في الجلسة، خدمة الرصيد وسجل الحركات، ربط المساعد الذكي، وصلاحيات التقييمات والمحادثات.</p><div class="note">بيانات المسافرين والمقاعد والدفع وإصدار التذاكر موزّعة أساساً على Dev 4 وDev 5. قد تظهر ملفاتها في مساهمات عمر بسبب تعديلات التكامل والمراجعة.</div></section>''')
pages.append('''<section class="page"><h2>كيف قُرئت قائمة الملفات؟</h2><h3>المصدر</h3><p>تم استخراج المسارات من commits غير الدمج المسجّلة بالبريد التالي:</p><p class="code">omar.altoom.ot@gmail.com</p><p>استُبعدت merge commits حتى لا يُنسب عمل زملاء الفريق إلى عمر لمجرد دمجه في فرعه. هذا الحصر لا يشمل تلقائياً تعديلات غير مسجّلة في Git أو commits بهوية مختلفة.</p><h3>الملفات الموجودة: 137</h3><p>مسارات وردت في سجل مساهمات عمر وتوجد حالياً بالمشروع. تشمل الواجهات، Controllers، Services، Models، Migrations، Seeders، JavaScript، CSS، الاختبارات، التوثيق والإعدادات.</p><h3>المسارات القديمة: 39</h3><p>مسارات وردت في مساهماته ثم حُذفت أو نُقلت أو أُعيدت تسميتها. حالة «قديم» مستنتجة من عدم وجود المسار حالياً، ولا تحدد وحدها سبب اختفائه.</p><h3>التفريق بين نطاق المهمة والمساهمة الفعلية</h3><p>نطاق Dev 3 يوضح مسؤوليتك حسب خطة الفريق. قائمة Git أوسع منه؛ فقد تشمل أعمال تأسيس قاعدة البيانات وتعديلات الربط بميزات يملكها مطورون آخرون.</p><h3>المراجع المحلية</h3><div class="refs">docs/TEAM_IMPLEMENTATION_PLAN_AR.md<br>docs/DEV3_STATUS.md<br>output/omar-files.txt</div><h3>أمر الحصر</h3><div class="refs">git log --no-merges --author=omar.altoom.ot@gmail.com --name-only --format=</div><div class="note">لا توجد مسارات vendor أو node_modules ضمن نتائج هذا الحصر. لم تُدرج صور أو محتويات الملفات؛ أُدرجت أسماؤها ومساراتها كاملة.</div></section>''')

def chunks(items, max_weight=45):
    current=[]; weight=0
    for n,path in items:
        w=max(1,(len(path)+93)//94)+0.35
        if current and weight+w>max_weight:
            yield current;current=[];weight=0
        current.append((n,path));weight+=w
    if current:yield current

for label,items in [('الملفات الموجودة حالياً',existing),('المسارات القديمة المحذوفة أو المنقولة',old)]:
    batches=list(chunks(list(enumerate(items,1))))
    for part,batch in enumerate(batches,1):
        files=''.join(f'<div class="file"><span class="idx">{n:03}</span><span class="path">{escape(p)}</span></div>' for n,p in batch)
        pages.append(f'<section class="page"><h2>{label}</h2><div class="label">الجزء {part} من {len(batches)} · الملفات {batch[0][0]} إلى {batch[-1][0]} من {len(items)}</div>'+files+'</section>')

for i in range(0,len(commits),15):
    content=''.join(f'<div class="commit">{escape(c)}</div>' for c in commits[i:i+15])
    pages.append(f'<section class="page"><h2>سجل commits عمر</h2><div class="label">الترتيب: الأحدث أولاً · الهاش | التاريخ | الرسالة</div>{content}</section>')

html='<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>Omar Al-Toom - Yalla Teer Contribution Report</title><style>'+css+'</style></head><body>'+''.join(pages)+'</body></html>'
(out/'Omar-YallaTeer-Contributions.html').write_text(html,encoding='utf-8')
print({'sections':len(pages),'existing':len(existing),'historical':len(old),'commits':len(commits)})
