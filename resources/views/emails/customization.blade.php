<h2>Customization Saved</h2>

<p>Your customized version of <strong>{{ $product->name }}</strong> has been added to your cart.</p>

<p>{{ count($savedFiles) }} image(s) customized:</p>

<ul>
@foreach($savedFiles as $file)
    <li><a href="{{ asset('uploads/customizations/'.$file) }}">{{ $file }}</a></li>
@endforeach
</ul>

<p>Go to your cart to complete checkout.</p>