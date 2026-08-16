<div class="grid justify-center gap-5" >
    <div class="flex flex-col justify-start gap-4 max-w-sm sm:max-xl md:max-w-2xl lg:max-w-4xl">
        <div class="flex flex-col gap-4 font-['Arimo']">
            <div class="flex flex-row gap-4">
                <div class="size-32">
                    <img class="max-w-32" src="{{asset("images/LB_logo.png")}}" />
                </div>
                <div class="w-full"> 
                    <div class="text-[#1b716c] text-5xl font-bold font-['Comfortaa']" >{{$getRecord()->name }}</div>
                    <div class="border border-[#1b716c]"> </div>
                    <div class="max-w-xl">{{ $getRecord()->description }}</div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <img class="max-h-60" src="{{Storage::url($getRecord()->icon)}}" alt="" />
                <div class="flex flex-col">
                    <div><img class="max-h-52" src="{{Storage::url($getRecord()->image)}}" alt="" /></div>
                    <table >
                        @foreach($getRecord()->features as $feature)
                        <tr class="odd:bg-olive-400 even:bg-olive-500">
                            <td><b>{{ $feature->name }}</b></td>
                            <td>{{ $feature->val }}</td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
            <div class="w-full bg-orange-100 mb-1 mx-2 font-['Comfortaa']">
                <div class="bg-teal-800 text-sm text-center py-2 font-bold">CONSEIL D'UTILISATION</div>
                <div class="py-2 "></ul>{!! str_replace("<ul>",'<ul class="list-disc list-inside">',$getRecord()->advice) !!}</div>
            </div>
            </div>
            <div class="w-full bg-orange-100 mb-1 mx-2 font-['Comfortaa']">
                <div class="bg-orange-500 text-sm text-center py-2 font-bold">PRECAUTION</div>
                <div class="py-2" >{!! str_replace("<ul>",'<ul class="list-disc list-inside">',$getRecord()->caution) !!}</div>
            </div>
        </div>
</div>