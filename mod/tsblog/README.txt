OU blog
=======

Copyright 2013 The Open University


This is an alternative blog that you can install into standard Moodle.

It does not replace the standard blog, and operates alongside it. There
are two modes of use:

1) As a course activity. In this case you can use it the same way as any
   other module. You can have course-wide blogs (everyone in the course posts
   to the same blog), group blogs, or individual blogs; the latter are
   useful for assessed activities (where the student is supposed to keep
   a journal which only they and their tutor can read).
   
2) As a replacement for standard Moodle personal blogs. In this case it would
   be a good idea to turn off the standard Moodle blog system, or it'll be
   very confusing. You then need to MANUALLY provide students with a link
   to mod/tsblog/view.php which will automatically take them to their
   personal blog.

When using for personal blogs, one feature of interest may be moderated public
comments; when you allow comments from people who are not logged in, all such
comments are moderated. 


Branch:

This stable branch is configured to match the latest stable branch at the
Open University that uses Moodle 2.5. Once we stop using Moodle 2.5, there
will be no more fixes in this branch (but of course anyone can fork the
reposistory and make a fixed version).

Support:

We cannot offer direct support. Please do not contact us directly. If you
need assistance, try the blog forum on moodle.org. (Remember to make clear
that you are using OU blog and not the standard blog.)

Documentation:

None. Please feel free to contribute documentation in the relevant area of
the MoodleDocs wiki.

Bug reports:

Please report bugs using the GitHub 'Issues' tab. Before reporting a bug,
please try the latest version to verify that the problem hasn't already
been fixed. In each bug, please remember to give:

1. Exact steps to reproduce your problem, starting from creating a new blog
   with default or specified settings.
2. The tsblog version you are using (from version.php or Modules admin screen).
3. The Moodle version you are using.

Reliability:

Please note that this code is tested on OU systems but we rely on the
community for testing on other systems.

Requires:

Moodle 2.5+
Postgres / MySQL

Install:

Place the contents of this source tree into your Moodle installation so that
within your Moodle root, this file is mod/tsblog/README. Then visit the
Moodle notifications page to install.

If you want the blogs to be searchable, you also need to install the
local_ousearch plugin. (It is best to do this before using OU blog much,
otherwise it takes ages to install as it builds indexes for everything.)
When you install the ousearch plugin, a search box will automatically appear.
