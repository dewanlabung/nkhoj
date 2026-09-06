<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; use Illuminate\Support\Str; ?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
    <channel>
        <title>nkhoj — नेपाली समाचार र ब्लग</title>
        <link>{{ url('/') }}</link>
        <description>Nepal's leading multi-blog and news platform</description>
        <language>ne</language>
        <atom:link href="{{ url('/feed.xml') }}" rel="self" type="application/rss+xml"/>
        <lastBuildDate>{{ now()->toRfc2822String() }}</lastBuildDate>
        @foreach($posts as $post)
        <item>
            <title><![CDATA[{{ $post->title }}]]></title>
            <link>{{ url('/posts/' . $post->slug) }}</link>
            <guid isPermaLink="true">{{ url('/posts/' . $post->slug) }}</guid>
            <description><![CDATA[{{ $post->excerpt ?? Str::limit(strip_tags($post->body), 300) }}]]></description>
            <pubDate>{{ ($post->published_at ?? $post->created_at)->toRfc2822String() }}</pubDate>
            @if($post->author)<author>{{ $post->author->email }} ({{ $post->author->name }})</author>@endif
            @if($post->category)<category>{{ $post->category->name_en }}</category>@endif
            @if($post->thumbnail_url)<media:content url="{{ $post->thumbnail_url }}" medium="image"/>@endif
        </item>
        @endforeach
    </channel>
</rss>
