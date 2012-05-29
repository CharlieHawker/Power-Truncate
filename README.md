What is Power Truncate?
=======================

Power truncate is a simple ExpressionEngine 1 & 2 add-on that allows truncation of plain text and HTML to a specified length.

Features
========

* Automatic closing of nested HTML tags
* Option to add suffix to end of truncated text
* Option to truncate at the end of a word or to split a word
* Specify truncation length in characters
* HTML tags ignored when caculating truncation position

Usage
=====

    {exp:weblog:entries weblog="news" limit="5"}
      {title}
      {exp:power_truncate length="200" cut_words="n" suffix="..."}
        {body}
      {/exp:power_truncate}
    {/exp:weblog:entries}

Developer
=========

I'm Charlie Hawker, find my blog here - http://www.toomanyredirects.com/
