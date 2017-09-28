
 var app = new Vue({
        el: '#courses-list-app',
        data: {
            freeSearch: "",
            semesterFilter: "",
            yearFilter: "",
            degreeFilter: "",
            courses: JSON.parse(Courses.courses),
            courseYears: JSON.parse(Courses.courseYears)
        },
        methods : {
            compareByCourseNum : function(a, b) {
                if (a.number > b.number) {
                    return 1;
                } else if (a.number < b.number) {
                    return -1;
                }
                return 0;
            },
            compareByActive: function(a, b) {
                if (a.active < b.active) {
                    return 1;
                } else if (a.active > b.active) {
                    return -1;
                }
                return 0;
            }
        },
        computed: {
            filteredCourses: function() {
                var self = this;
                return self.courses.filter(function(course) {
                    return (course.name.indexOf(self.freeSearch) > -1 ||
                        course.number.indexOf(self.freeSearch) > -1 ||
                        course.lecturer_name.indexOf(self.freeSearch) > -1) &&
                        (course.year === self.yearFilter || self.yearFilter == "") &&
                        (course.degree === self.degreeFilter || self.degreeFilter == "")                        
                }).sort(self.compareByCourseNum).sort(self.compareByActive);
            }
        }
    })