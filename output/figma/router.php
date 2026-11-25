<?php
$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
if ($path==='/__figma-gallery') {
    $groups=[
        'test'=>['/login','/register'],
        'extras'=>['/my/assistant/1','/my/bookings/DEMO0100/review','/__preview-error/403','/__preview-error/404','/__preview-error/500'],
        'public'=>['/','/destinations','/flights','/flights/1','/compare','/booking-lookup','/login','/register','/about','/faq','/contact','/terms','/privacy','/admin/login','/airline/login'],
        'customer'=>['/my/bookings','/my/bookings/DEMO0000','/my/tickets/DEMO0000O0','/my/credits','/my/profile','/my/assistant','/my/assistant/1','/my/booking-flights/5/review','/book/passengers','/book/seats','/book/review','/book/payment','/book/confirmation'],
        'admin'=>['/admin','/admin/bookings','/admin/bookings/1','/admin/credits','/admin/flights','/admin/payments','/admin/cities','/admin/cities/create','/admin/cities/1/edit','/admin/airports','/admin/airports/create','/admin/airports/1/edit','/admin/countries','/admin/countries/create','/admin/countries/1/edit'],
        'management'=>['/admin/airlines','/admin/airlines/create','/admin/airlines/1/edit','/admin/airline-admins','/admin/airline-admins/create','/admin/airline-admins/1/edit','/admin/customers','/admin/customers/1','/admin/reviews','/admin/ai-conversations','/admin/ai-conversations/1','/admin/notifications','/admin/messages'],
        'airline'=>['/airline','/airline/flights','/airline/flights/create','/airline/flights/1','/airline/flights/1/edit','/airline/bookings','/airline/bookings/1','/airline/reviews'],
    ];
    $group=$_GET['group']??'test';
    header('Content-Type:text/html;charset=utf-8');
    echo '<!doctype html><html><head><title>YallaTeer '.htmlspecialchars($group).'</title><script src="https://mcp.figma.com/mcp/html-to-design/capture.js" async></script></head><body style="margin:0;background:#282828;width:1440px">';
    echo '<div id="screens"></div><script>const routes='.json_encode($groups[$group]??[]).'; Promise.all(routes.map(async route=>({route,html:await(await fetch(route)).text()}))).then(pages=>{ for(const {route,html} of pages){const d=new DOMParser().parseFromString(html,"text/html");for(const l of d.querySelectorAll("link[rel=stylesheet],style"))document.head.append(l.cloneNode(true));const label=document.createElement("h2");label.textContent=route;label.style="color:white;font:24px sans-serif;margin:30px 0";const root=document.createElement("div");root.className=d.body.className;root.dataset.route=route;root.lang="en";root.style="width:1440px;min-height:1000px;position:relative;display:flex;flex-direction:column;background:#0e0d0b;color:#faf5ec;overflow:hidden";for(const s of d.body.querySelectorAll("script"))s.remove();root.innerHTML=d.body.innerHTML;document.querySelector("#screens").append(label,root);for(const el of root.querySelectorAll("[data-reveal]")){el.style.opacity=1;el.style.transform="none"}for(const el of root.querySelectorAll(".assistant-shortcut"))el.style.position="absolute";}document.title+=" READY "+pages.length;});</script></body></html>'; return;
}
$asset=realpath(__DIR__.'/../../public'.$path);
if ($path!=='/' && $asset && is_file($asset) && str_starts_with($asset,realpath(__DIR__.'/../../public'))) return false;
$app=require __DIR__.'/bootstrap.php';
$app['router']->get('/__preview-error/{status}',fn($status)=>response()->view('errors.'.$status,[],200));
class FigmaPreviewIdentity {
    public function handle($request, $next) {
        $path=$request->path();
        $email=match(true) {
            str_starts_with($path,'admin') && $path!=='admin/login'=>'admin@demo.example.test',
            str_starts_with($path,'airline') && $path!=='airline/login'=>'staff.rj@demo.example.test',
            str_starts_with($path,'my/') || str_starts_with($path,'book/') || $path==='assistant'=>'traveler@demo.example.test',
            default=>null
        };
        if ($email) Illuminate\Support\Facades\Auth::setUser(App\Models\User::where('email',$email)->firstOrFail());
        if (str_starts_with($path,'__preview-error/')) return response()->view('errors.'.basename($path),[],200);
        if (str_starts_with($path,'book/')) {
            $flight=App\Models\Flight::where('departure_at','>',now())->where('status','scheduled')->firstOrFail();
            $cabin=$flight->flightClasses()->where('class','economy')->firstOrFail();
            $selection=['trip'=>'one_way','passengers'=>1,'passenger_counts'=>['adults'=>1,'youth'=>0,'children'=>0],'outbound'=>['flight_id'=>$flight->id,'flight_class_id'=>$cabin->id],'return'=>null];
            $passenger=['first_name'=>'Sara','last_name'=>'Demo','date_of_birth'=>'1992-06-15','gender'=>'female','nationality'=>'Jordanian','passport_number'=>'TESTPREVIEW','passport_country'=>'Jordan','passport_expiry'=>'2031-01-01','special_assistance'=>null,'filled_by_ai'=>false,'type'=>'adult'];
            $request->session()->put('dev3.search_selection',$selection);
            $request->session()->put('dev4.passenger_draft',['selection_id'=>hash('sha256',serialize($selection)),'booking_code'=>'FIGMAPREVIEW','details'=>Illuminate\Support\Facades\Crypt::encrypt(['passengers'=>[$passenger],'contact_email'=>'traveler@demo.example.test','contact_phone'=>'+962700000000'])]);
            if (in_array($path,['book/review','book/payment','book/confirmation'])) {
                $booking=$request->user()->customer->bookings()->where('status',$path==='book/confirmation'?'confirmed':'pending')->firstOrFail();
                $request->query->set('booking_id',$booking->id);
            }
        }
        return $next($request);
    }
}
$app->make(Illuminate\Contracts\Http\Kernel::class)->appendMiddlewareToGroup('web',FigmaPreviewIdentity::class);
$app->handleRequest(Illuminate\Http\Request::capture());
