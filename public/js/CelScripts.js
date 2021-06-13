(function () {
angular.module("celebrosUI", ["ngRoute"]);

angular
    .module("celebrosUI")
    .config(["$routeProvider", "$locationProvider", "$sceDelegateProvider", function ($routeProvider, $locationProvider, $sceDelegateProvider) {
        $routeProvider
            .otherwise({
                template: "<div id=\"profile-tabs\" ng-show=\"settings.General.ProfileTabs !== ''\" class=\"ng-cloak\">    <ul>        <li cel-profile ng-repeat=\"(name, tab) in settings.General.ProfileTabs\" profile=\"{{ tab.Profile }}\" display=\"{{ tab.Display }}\" ng-class=\"{ 'selected' : profile == tab.Profile }\" tabindex=\"0\">            <span>{{ tab.Alias !== undefined ? tab.Alias : name }}</span>        </li>    </ul></div><div id=\"loader\" ng-hide=\"loading == false\">    <div><i class=\"cel-icon-spin3 animate-spin\"></i></div></div>        <div id=\"filter-details\">            <div ng-show=\"searchPage === true\" id=\"filter-title\">{{ settings.Language.Search }} \"{{ results.OriginalQuery }}\" </div>            <div id=\"products-count\">{{ results.ProductsCount }} {{ settings.Language.Products }}</div>        </div><div cel-search-anlx cel-infinite-scroll=\"{{ settings.General.InfiniteScroll }}\" id=\"container\" class=\"flex-box-row {{ profile !== '' ? 'profile-' + profile : '' }}\" ng-hide=\"loading == true\">    <div id=\"filter-content\">        <div id=\"filter-breadcrumbs\" ng-show=\"results.SearchPath.Answers.length != 0\" class=\"ng-cloak\">            <div class=\"filter-by\">{{ settings.Language.FilterBy }}</div>            <ul class=\"breadcrumbs\">                <li cel-breadcrumb ng-repeat=\"breadcrumb in results.SearchPath.Answers\" id=\"{{ breadcrumb.ID }}\" class=\"breadcrumb\" answer-id=\"{{ breadcrumb.ID }}\" tabindex=\"0\">                    <!-- Template in breadcrumb.html -->                </li>            </ul>            <div id=\"clear-all\">                <button class=\"btn-default\" ng-click=\"clearAll()\">{{ settings.Language.ClearAll }}</button>            </div>        </div>        <div id=\"filters\">            <div id=\"questions-toggle\" class=\"hidden visible-sm visible-xs\">                <span cel-toggle=\"toggleQuestions\" tabindex=\"0\"><i ng-class=\"{ 'cel-icon-angle-down' : toggleQuestions == false, 'cel-icon-angle-up' : toggleQuestions == true }\"></i>{{ settings.Language.Filters }}</span>            </div>            <div ng-hide=\"settings.Refinements.InstantAnswerMode == true || showApply == false\" class=\"filter-apply\">                <span cel-filter-apply>{{ settings.Langauge.Apply }}</span>            </div>            <div id=\"questions-container\" ng-class=\"{ 'hidden-xs hidden-sm' : toggleQuestions == false }\">                <ul class=\"questions\">                    <li cel-question ng-repeat=\"question in results.Questions\" id=\"{{ question.SideText }}\">                        <!-- Template in question.html -->                    </li>                    <rzslider cel-price-slider ng-if=\"settings.Refinements.EnablePriceSlider == true\" ng-show=\"showSlider === true\" class=\"hidden-xs hidden-sm\"                                rz-slider-model=\"slider.minValue\"                                rz-slider-high=\"slider.maxValue\"                                rz-slider-options=\"slider.options\"></rzslider>                </ul>            </div>            <div ng-hide=\"settings.Refinements.InstantAnswerMode == true || showApply == false\" class=\"filter-apply\">                <span cel-filter-apply>{{ settings.Language.Apply }}</span>            </div>        </div>    </div>    <div id=\"main-content\">        <div cel-campaigns id=\"campaigns\"></div>        <div id=\"messages\" ng-show=\"results.RecommendedMessage != '' && results.RecommendedMessage !== undefined\" class=\"ng-cloak\">            <span ng-bind-html=\"results.RecommendedMessage | trust_html\"></span>        </div>        <div id=\"related-searches\" ng-if=\"results.RelatedSearches.length > 0\"><a ng-repeat=\"query in results.RelatedSearches\" href=\"?{{ settings.General.DoSearchUrlParameter }}={{ query }}\">{{ query }}</a></div>        <div class=\"toolbar flex-box-row col-xs-max-1 col-sm-max-3 col-md-max-3 col-max-3\">                      <div id=\"display\" class=\"hidden-sm\">                <ul class=\"flex-box-row\">                    <li cel-display=\"grid\" class=\"grid_list grid\" ng-class=\"{ selected : display == 'grid' }\" ng-attr-tabindex=\"{{display == 'grid' ? '': '0'}}\" title=\"{{ settings.Language.Grid }}\">                        <i class=\"cel-icon-grid\"></i>                    </li>                    <li cel-display=\"list\" class=\"grid_list list\" ng-class=\"{ selected : display == 'list' }\" ng-attr-tabindex=\"{{ display == 'list' ? '': '0' }}\" title=\"{{ settings.Language.List }}\">                        <i class=\"cel-icon-list\"></i>                    </li>                </ul>            </div><div id=\"search\"><input type=\"text\" value=\"\" id=\"search-products\" placeholder=\"Search Key\" ng-keyup= \"searchProducts($event)\"><span ng-click= \"searchProducts()\" ><i class=\"cel-icon-search\"></i></span></div>                         <div id=\"sorting\">            	<div class=\"select-wrapper cel-icon-angle-down\">Sort By 	            	<select cel-sort >	            		<option ng-repeat=\"(name, sort) in settings.Toolbar.SortOptions\" ng-selected=\"currentSort.alias === sort.Alias\" t=\"{{ currentSort.alias }} {{ sort.Alias }}\" ng-attr-tabindex=\"{{ currentSort.alias == sort.Alias && sort.SingleOrder !== undefined ? '': '0'}}\">{{ settings.Language[name] !== undefined ? settings.Language[name] : sort.Alias }}</option>	            	</select>	                <!--<select ng-if=\"searchPage === false\" class=\"flex-box-row\" onChange=\"location = this.value\">	                    <option cel-sort-option ng-repeat=\"(name, sort) in settings.Toolbar.SortOptions\" ng-attr-tabindex=\"{{ currentSort.alias == sort.Alias && sort.SingleOrder !== undefined ? '': '0'}}\" ng-value=\"sort.NavURL\" ng-selected=\"currentSort.alias === sort.Alias\">{{ sort.Alias }}</option>	                </select>-->                </div>            </div>                                             </div><div style=\"display:flex; align-items: center;\"><input type=\"checkbox\" ng-click=\"checkAll($event)\" class=\"check-all-products\"><button ng-click=\"allAddToImportList($event)\" type=\"submit\" title=\"{{ settings.Language.AddToImportList }}\" class=\"all-add-products\">{{ settings.Language.AddToImportList }}</button></div>        <ul class=\"products products-{{ display }} flex-box-row\" ng-class=\"{'col-xs-max-2 col-sm-max-3 col-md-max-3 col-lg-max-3 col-max-4' : display=='grid',  'col-max-1' : display == 'list' }\">            <li cel-product ng-repeat=\"product in results.Products\" id=\"{{ product.Id }}\" class=\"cuit_item product flex-box-column product-item\">                <!-- Template in product.html -->            </li>        </ul>        <div class=\"toolbar toolbar-bottom flex-box-row col-xs-max-1 col-sm-max-3 col-max-2\">            <div cel-pagination ng-hide=\"results.PagesCount == 1 || settings.ProductResults.InfiniteScroll == true\" id=\"pagination\" current-page=\"{{ results.CurrentPage }}\" pages-count=\"{{ results.PagesCount }}\">                <!-- Template in pagination.html -->            </div><div id=\"page-sizer\" class=\"hidden-xs hidden-sm hidden-md\">                <div class=\"select-wrapper cel-icon-angle-down\" ng-if=\"settings.Toolbar.PageSizes.Enabled == true && settings.ProductResults.InfiniteScroll == false\">                    <span>{{ settings.Language.ProductsPerPage }}</span>                    <select cel-page-sizer class=\"cel-icon-angle-down\">                        <option ng-repeat=\"size in settings.Toolbar.PageSizes.Option\" id=\"{{ size.Value }}\" ng-selected=\"{{ size.Value == pageSize }}\" ng-value=\"{{ size.Value }}\">{{ size.Value }}</option>                    </select>                </div>            </div></div>    </div></div><div cel-to-top ng-if=\"settings.ProductResults.InfiniteScroll == true && showToTop == true\" class=\"to-top cel-icon-angle-up\" ng-attr-tabindex=\"{{settings.ProductResults.InfiniteScroll == true ? '': '0'}}\"></div>",
                controller: "CelController",
                reloadOnSearch: false
            });
        $locationProvider.html5Mode({
            enabled: true
        }).hashPrefix('');
    }]);
angular
    .module("celebrosUI")
    .factory("celAnlx", ['$http', 'celConfig', function ($http, celConfig) {
        var anlxUrl = "//" + celConfig.Settings.General.AnalyticsInterfaceServer + "/AIWriter/WriteLog.ashx";

        var sendAnlx = function (data) {
            $http.get(anlxUrl, { params: data })
                .then(function (response) {
                    return response;
                }, function (error) {
                    console.log(error);
                });
        };

        return {
            sendAnlx: sendAnlx
        };
    }]);

angular
    .module("celebrosUI")
    .directive("celAnswer", ['$location', 'celAPI', 'celConfig', function ($location, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<span ng-if=\"question.SideText !== 'ColorSwatches'\" class=\"answer-text\" ng-bind-html=\"answer.Text | trust_html\"></span><span ng-if=\"question.SideText !== 'ColorSwatches'\" class=\"answer-products-count pull-right\">({{ answer.ProductCount }})</span><span ng-if=\"question.SideText === 'ColorSwatches'\" class=\"answer-color\" ng-style=\"{ 'background-color': answer.Code }\" alt=\"{{ answer.Text }}\"></span>",
            link: function (scope, elem, attr) {

            	if(scope.$parent.question.SideText === "ColorSwatches") {
            		var colorMap = scope.answer.Text;
            		scope.answer.Code = colorMap.split(";")[0];
            		scope.answer.Text = colorMap.split(";")[1];
            	}

            	if(scope.$parent.question.SideText === "Price") {
            		var PriceText  = scope.answer.Text.replace('$','').replace('$','');
            		const options2 = { style: 'currency', currency: 'USD' };
					const numberFormat2 = new Intl.NumberFormat('en-US', options2);

            		 if (!scope.answer.Text.indexOf("under")){
            			scope.answer.Text = scope.answer.Text.replace("under $",'');
            			scope.answer.Text = numberFormat2.format(scope.answer.Text);
            			scope.answer.Text = "Under " + scope.answer.Text;
            		 }
            		 else if (!scope.answer.Text.indexOf("over")){
            		 	scope.answer.Text = scope.answer.Text.replace("over $",'');
            			scope.answer.Text = numberFormat2.format(scope.answer.Text);
            			scope.answer.Text = "Over " + scope.answer.Text;
            		 }
            		 else{

            			var Priceleft = PriceText.split("-")[0];
            			Priceleft =  numberFormat2.format(Priceleft);
            			var Priceright = PriceText.split("-")[1];
            			Priceright = numberFormat2.format(Priceright);
            			scope.answer.Text = Priceleft +' - '+ Priceright
            		 }
            	}

                var selectAnswer = function (answer) {
                    var i;
                    /* This is a hacked way of getting to the celController scope so we can modify the same scope.loading object */
                    var parent = scope;
                    var parentId = scope.$id;
                    while (parent.$parent && parent.$id != scope.mainScopeId) {
                        parent = parent.$parent;
                        parentId = parent.$id;
                    }

                    if (celConfig.Settings.EnableLoader) {
                        parent.loading = true;
                    }

                    var answerID = answer.attributes["answer-id"].value;
                    var questionText = scope.$parent.question.Name;
                    var hierarchical = scope.$parent.question.IsHierarchical;

                    var addProduct = true;

                    if ((!scope.$parent.question.enabled || parent.disableQuestions) && parent.selectedAnswers.indexOf(answerID) < 0) {
                        return;
                    }

                    scope.data.answerId = answerID;

                    /* Check if this is already a selected answer */
                    if (questionText === undefined) {
                        for (key in $location.search()) {
                            if ($location.search()[key].indexOf(answerID) > -1) {
                                questionText = key;
                            }
                        }
                    }

                    //for (var i = 0; i < scope.results.SearchPath.Answers.length; i++) {
                    //    if (scope.results.SearchPath.Answers[i].ID == answerID) {
                    //        addProduct = false;
                    //        break;
                    //    }
                    //}

                    if (parent.selectedAnswers.indexOf(answerID) > -1) {
                        addProduct = false;
                    } else {
                        addProduct = true;
                    }


                    $location.search("Page", null);

                    parent.showApply = true;

                    if (addProduct) { /* Add Answer */
                        scope.$apply(function () {
                            /* Used to send to the API function */
                            scope.data.answerIds.push(answerID);

                            /* Update selected Answer array */
                            parent.selectedAnswers.push(answerID);

                            /* Update the search portion of the URL */
                            if ($location.search().hasOwnProperty(questionText) && $location.search()[questionText].length > 0) {
                                $location.search(questionText, $location.search()[questionText] + "," + answerID);
                            } else {
                                $location.search(questionText, answerID);
                            }

                            /* Push or Replace the state based on InstantAnswerMode */
                            if (celConfig.Settings.Refinements.InstantAnswerMode) {
                                celAPI.doAnswerQuestion(scope.data).then(scope.onResults, scope.onError);
                                history.pushState(scope.data, null, $location.absUrl());
                            } else {
                                for (i = 0; i < parent.results.Questions.length; i++) {
                                    if (parent.results.Questions[i].IsHierarchical && hierarchical) {
                                        parent.results.Questions[i].enabled = false;
                                    }
                                }
                                //scope.$parent.question.showApply = true;

                                /* Update Breadcrumbs */
                                parent.results.SearchPath.Answers.push({ "ID": answerID, "Name": scope.answer.Name, "QuestionText": questionText, "QuestionId": scope.$parent.question.ID });


                                history.replaceState(scope.data, null, $location.absUrl());
                            }
                        });
                    } else { /* Remove Answer */
                        scope.$apply(function () {
                            /* Used to send to the API function */
                            var index = scope.data.answerIds.indexOf(answerID);
                            if (index > -1) {
                                scope.data.answerIds.splice(index, 1);
                            }

                            /* Update selected Answer array */
                            index = parent.selectedAnswers.indexOf(answerID);
                            if (index > -1) {
                                parent.selectedAnswers.splice(index, 1);
                            }

                            /* Update the search portion of the URL */
                            if ($location.search().hasOwnProperty(questionText)) {
                                var values = $location.search()[questionText];
                                if (!Array.isArray(values)) {
                                    values = values.split(",");
                                }
                                var newValues = [];
                                if (Array.isArray(values) && values.length > 1) {
                                    for (i = 0; i < values.length; i++) {
                                        if (values[i] === answerID)
                                            continue;
                                        newValues.push(values[i]);
                                    }
                                    $location.search(questionText, newValues.toString());
                                } else {
                                    $location.search(questionText, null);
                                }
                            }

                            /* Push or Replace the state based on InstantAnswerMode */
                            if (celConfig.Settings.Refinements.InstantAnswerMode) {
                                celAPI.doRemoveAnswer(scope.data).then(scope.onResults, scope.onError);
                                history.pushState(scope.data, null, $location.absUrl());
                            } else {
                                for (i = 0; i < parent.results.Questions.length; i++) {
                                    if (parent.results.Questions[i].IsHierarchical && hierarchical) {
                                        parent.results.Questions[i].enabled = true;
                                    }
                                }
                                //scope.$parent.question.showApply = false;

                                /* Update Breadcrumbs */
                                index = -1;
                                for (i = 0; i < parent.results.SearchPath.Answers.length; i++) {
                                    if (answerID === parent.results.SearchPath.Answers[i].ID) {
                                        index = i;
                                    }
                                }
                                parent.results.SearchPath.Answers.splice(index, 1);
                                history.replaceState(scope.data, null, $location.absUrl());
                            }
                        });
                    }
                    /* if (scope.answer.DynamicProperties.length>0)
                     {

                     }*/

                };

                elem.bind("click", function (event) {
                    event.stopPropagation();
                    selectAnswer(this);
                });
                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        event.stopPropagation();
                        selectAnswer(this);
                    }
                });


            }
        };
    }]);

angular
    .module("celebrosUI")
    .factory("celAPI", ['$http', '$q', 'celConfig', function ($http, $q, celConfig) {

        var wcfServer = celConfig.Settings.General.WcfAddress;
        wcfServer = wcfServer.replace(/http:/ig, "");
        if (wcfServer.indexOf("//") !== 0) {
            wcfServer = "//" + wcfServer;
        }

        if (wcfServer.indexOf(":446") >= 0) {
            wcfServer = "https:" + wcfServer;
        }

        if (window.location.protocol === "https:") {
            wcfServer = wcfServer.replace(":8080", ":8081");
        }

        /* This is used to remove invalid parameters from the request
         *
         * It doesn't really need to be done, since the API will ignore invalid parameters,
         * but it makes it easier when debugging to only have the parameters that are being used
         * */
        var apiParamMapping = {
            /* doSearch answerId is removed so when instant search is enabled, it won't retain any answer ids */
            Search: ["query", "siteKey", "principles"],
            doSearch: ["siteId", "searchHandle", "query", /*"answerId",*/ "pageSize", "fieldName", "isAscending", "isNumericSort", "profile", "principles"],
            doSearchParams: ["siteId", "searchHandle", "query", "answerIds", "page", "pageSize", "fieldName", "isAscending", "isNumericSort", "profile", "priceField", "effectOnSearchPath", "principles"],
            doChangeProductsPerPage: ["siteId", "searchHandle", "pageSize", "principles"],
            doSortByField: ["siteId", "searchHandle", "fieldName", "isAscending", "isNumericSort", "principles"],
            doMovePage: ["siteId", "searchHandle", "pageNumber", "principles"],
            doAnswerQuestion: ["siteId", "searchHandle", "answerId", "principles"],
            doRemoveAnswer: ["siteId", "searchHandle", "answerId", "principles"],
            doSortByName: ["siteId", "searchHandle", "isAscending", "isNumericSort", "principles"],
            doSortByRank: ["siteId", "searchHandle", "principles"],
            doSortByPrice: ["siteId", "searchHandle", "isAscending", "principles"]
        };

        var Search = function (data) {
            var canceller = $q.defer();
            var cancel = function (reason) {
                canceller.resolve(reason);
            };

            var promise = $http.get(wcfServer + "DoSearch", { params: data, timeout: canceller.promise })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSearchResult);
                    if (data.query == '') {
                        return result;
                    } else {
                        var params = {
                            siteId: data.siteId,
                            searchHandle: result.Handle,
                            answerId: result.Questions[0].Answers[0].ID
                        }
                        return doAnswerQuestion(params).then(function (res) {
                            return res;
                        });
                    }
                });

            return {
                promise: promise,
                cancel: cancel
            };
        }

        var doSearch = function (data) {
            data = JSON.parse(JSON.stringify(data));

            var canceller = $q.defer();

            var cancel = function (reason) {
                canceller.resolve(reason);
            };

            for (var param in data) {
                if (apiParamMapping.doSearch.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }

            if (data.fieldName === "Relevancy") {
                delete data.fieldName;
                delete data.isAscending;
                delete data.isNumericSort;
            }

            if (data.profile === null || data.profile === undefined || data.profile === "") {
                data.profile = "SiteDefault";
            }

            var promise = $http.get(wcfServer + "DoSearch", { params: data, timeout: canceller.promise })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSearchResult);
                    return result;
                });

            return {
                promise: promise,
                cancel: cancel
            };
        };

        var doSearchParams = function (data) {
            data = JSON.parse(JSON.stringify(data));
            for (var param in data) {
                if (apiParamMapping.doSearchParams.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }

            if (data.fieldName === "Relevancy") {
                delete data.fieldName;
                delete data.isAscending;
                delete data.isNumericSort;
            }

            if (data.profile === null || data.profile === undefined || data.profile === "") {
                data.profile = "SiteDefault";
            }

            if (data.answerIds !== null && data.answerIds !== undefined) {
                data.answerIds = data.answerIds.toString();
            }

            if (data.effectOnSearchPath === undefined || data.effectOnSearchPath === null) {
                data.effectOnSearchPath = 1;
            }

            return $http.get(wcfServer + "DoSearchParams", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSearchParams);
                    return result;
                });
        };

        var doChangeProductsPerPage = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doChangeProductsPerPage.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }

            return $http.get(wcfServer + "DoChangeProductsPerPage", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoChangeProductsPerPageResult);
                    return result;
                });
        };

        var doAnswerQuestion = function (data) {
            data = JSON.parse(JSON.stringify(data));
            for (var param in data) {
                if (apiParamMapping.doAnswerQuestion.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoAnswerQuestion", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoAnswerQuestionResult);
                    return result;
                });
        };

        var doRemoveAnswer = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doRemoveAnswer.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoRemoveAnswer", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoRemoveAnswerResult);
                    return result;
                });
        };

        var doMovePage = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doMovePage.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoMovePage", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoMovePageResult);
                    return result;
                });
        };

        var doSortByField = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doSortByField.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoSortByField", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSortByFieldResult);
                    return result;
                });
        };

        var doSortByName = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doSortByName.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoSortByName", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSortByNameResult);
                    return result;
                });
        };

        var doSortByRank = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doSortByRank.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoSortByRank", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSortByRankResult);
                    return result;
                });
        };

        var doSortByPrice = function (data) {
            data = JSON.parse(JSON.stringify(data));

            for (var param in data) {
                if (apiParamMapping.doSortByPrice.indexOf(param) < 0 || data[param] === null) {
                    delete data[param];
                }
            }
            return $http.get(wcfServer + "DoSortByPrice", { params: data })
                .then(function (response) {
                    var result = filterCategories(response.data.DoSortByPriceResult);
                    return result;
                });
        };

        /* getPrice is for Magento Clients only */
        var getPrice = null;
        if (typeof celConfig.Settings.Magento !== "undefined") {
            var priceAPI = celConfig.Settings.Magento.RealTimePriceURL.replace("http:", "");

            getPrice = function (data) {
                var skus = { 'skus': data.toString() };

                return $http.jsonp(priceAPI, { params: skus, jsonpCallbackParam: "callback" })
                    .then(function (response) {
                        return response.data;
                    });
            };
        }

        var filterCategories = function (data) {
            var answers = [];
            var products_count = 0;
            if (data.SearchPath[0].Answers.length == 0) {
                data.Questions[0].Answers.forEach(question => {
                    if (question.Name == 'Grocery' || question.Name == 'Beauty & Body Care' || question.Name == 'Vitamins & Supplements' || question.Name == 'Home Products' || question.Name == 'Health' || question.Name == 'Pet') {
                        answers.push(question);
                        products_count += question.ProductCount;
                    }
                });
                data.Questions[0].ExtraAnswers.forEach(question => {
                    if (question.Name == 'Grocery' || question.Name == 'Beauty & Body Care' || question.Name == 'Vitamins & Supplements' || question.Name == 'Home Products' || question.Name == 'Health' || question.Name == 'Pet') {
                        answers.push(question);
                        products_count += question.ProductCount;
                    }
                });
                data.Questions[0].Answers = answers;
                data.Questions[0].ExtraAnswers = [];
                data.ProductsCount = products_count;
            }
            return data;
        }

        return {
            Search: Search,
            doSearch: doSearch,
            doSearchParams: doSearchParams,
            doChangeProductsPerPage: doChangeProductsPerPage,
            doAnswerQuestion: doAnswerQuestion,
            doRemoveAnswer: doRemoveAnswer,
            doMovePage: doMovePage,
            doSortByField: doSortByField,
            doSortByName: doSortByName,
            doSortByRank: doSortByRank,
            doSortByPrice: doSortByPrice,
            getPrice: getPrice
        };
    }]);

angular
    .module("celebrosUI")
    .directive("celBreadcrumb", ['$location', 'celAPI', 'celConfig', function ($location, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<span ng-if=\"breadcrumb.QuestionText !== 'Color'\" class=\"cel-icon-cancel\">{{ breadcrumb.Name }}</span><span ng-if=\"breadcrumb.QuestionText === 'Color'\" class=\"cel-icon-cancel breadcrumb-color\" ng-style=\"{ 'background-color': breadcrumb.Name }\"></span>",
            link: function (scope, elem, attr) {

            	var answerID = scope.breadcrumb.ID;
            	var questionText;
            	for (key in $location.search()) {
                        if (isNaN($location.search()[key]) && $location.search()[key].indexOf(scope.breadcrumb.ID) > -1) {
                            scope.breadcrumb.QuestionText = questionText = key;
                        } else if (!isNaN($location.search()[key]) && $location.search()[key] === answerID) {
                            scope.breadcrumb.QuestionText = questionText = key;
                        }
                    }

                var selectBreadcrumb = function (breadcrumb) {

                    /* This is a hacked way of getting to the celController scope so we can modify the same scope.loading object */
                    var parent = scope;
                    var parentId = scope.$id;
                    while (parent.$parent && parent.$id != scope.mainScopeId) {
                        parent = parent.$parent;
                        parentId = parent.$id;
                    }

                    if (celConfig.Settings.EnableLoader) {
                        scope.$parent.loading = true;
                    }

                    //var answerID = breadcrumb.attributes["answer-id"].value;
                    //var questionText;

                    var data = JSON.parse(JSON.stringify(scope.data));
                    if (data.answerIds !== undefined) {
                        delete data.answerIds;
                    }
                    data.answerId = answerID;

                    //for (key in $location.search()) {
                    //    if (isNaN($location.search()[key]) && $location.search()[key].indexOf(answerID) > -1) {
                    //        questionText = key;
                    //    } else if (!isNaN($location.search()[key]) && $location.search()[key] === answerID) {
                    //        questionText = key;
                    //    }
                    //}

                    scope.$apply(function () {
                        parent.showApply = true;

                        /* Used to send to the API function */
                        var index = scope.data.answerIds.indexOf(answerID);
                        if (index > -1) {
                            scope.data.answerId = scope.data.answerIds.splice(index, 1);
                        }

                        ///* Update selected Answer array */
                        //index = parent.selectedAnswers.indexOf(answerID);
                        //if (index > -1) {
                        //parent.selectedAnswers.splice(index, 1);
                        //}

                        ///* Update Breadcrumbs */
                        //index = -1;
                        //for (var i = 0; i < parent.results.SearchPath.Answers.length; i++) {
                        //if (answerID == parent.results.SearchPath.Answers[i].ID) {
                        //index = i;
                        //}
                        //}
                        //if (index >= 0) {
                        //parent.results.SearchPath.Answers.splice(index, 1);
                        //}

                        $location.search("Page", null);

                        if ($location.search().hasOwnProperty(questionText)) {
                            var values = $location.search()[questionText];
                            if (!Array.isArray(values)) {
                                values = values.split(",");
                            }
                            var newValues = [];
                            if (Array.isArray(values)) {
                                for (var i = 0; i < values.length; i++) {
                                    if (values[i] === answerID)
                                        continue;
                                    newValues.push(values[i]);
                                }
                                $location.search(questionText, newValues);
                            } else {
                                $location.search(questionText, null);
                            }
                        }
                        if (celConfig.Settings.Refinements.InstantAnswerMode) {
                            celAPI.doRemoveAnswer(scope.data).then(scope.onResults, scope.onError);
                            history.pushState(scope.data, null, $location.absUrl());
                        } else {
                            history.replaceState(scope.data, null, $location.absUrl());
                        }
                    });
                };

                elem.bind("click", function () {
                    selectBreadcrumb(this);
                });

                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        selectBreadcrumb(this);
                    }
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celCampaigns", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<span ng-if=\"campaign.Type == 'customer message'\" >{{ campaign.Value }}</span><img ng-if=\"campaign.Type == 'banner image'\" ng-src=\"{{ campaign.Value }}\" /><a ng-if=\"campaign.Type == 'banner landing page'\" href=\"{{ campaign.Target }}\"><img ng-src=\"{{ campaign.Value }}\" /></a><div ng-if=\"campaign.Type == 'banner html'\" ng-bind-html=\"campaign.Value | trust_html\"></div>",
            link: function (scope, elem, attr) {

                var campaigns = function () {
                    if (scope.results.QueryConcepts === undefined ) {
                        return;
                    }
                    scope.campaign = {};
                    var dpTypes = ["banner html", "banner image", "banner landing page", "custom message"];
                    if (scope.results.QueryConcepts.length > 0) {
                        for (var i = 0; i < scope.results.QueryConcepts.length; i++) {
                            var concept = scope.results.QueryConcepts[i];
                            for (var j = 0; j < concept.DynamicProperties.length; j++) {
                                var dp = concept.DynamicProperties[j];
                                if (dpTypes.indexOf(dp.Name) >= 0) {
                                    if (dp.Name !== "banner landing page") {
                                        if (scope.campaign.Type !== "banner landing page") {
                                            scope.campaign.Type = dp.Name;
                                        }
                                        scope.campaign.Value = dp.Value;
                                    } else {
                                        scope.campaign.Type = dp.Name;
                                        scope.campaign.Target = dp.Value;
                                    }
                                }
                            }
                        }
                    }
                };

                scope.$watch("query", function () {
                    campaigns();
                });
            }
        };
    });
angular
    .module("celebrosUI")
    .factory("celConfig", function () {

        /*
         * root IS AUTO GENERATED WHEN COMPILED. DO NOT EDIT!
         */
        var root = {"?xml":{"@version":"1.0","@encoding":"utf-8"}/*V:1.1.6*/,"Settings":{"General":{"#comment":[],"SiteAddress":"GreenDropS-search.celebros.com","SitePort":"6035","SiteKey":"GreenDropS","API":"Default","WcfAddress":"//GreenDropS-search.celebros.com/UiSearch/","Language":"EN","AnalyticsInterfaceServer":"ai.celebros-analytics.com","AnalyticsCustomerName":"GreenDropS","Profile":"","EmptyQueryProfile":"","ProfileTabs":"","TemplateName":"SideKat","DoSearchUrlParameter":"q","PageSize":"12","ClientFolder":"GreenDropS","InstantSearch":"false","UrlRegexPattern":".+(?=[.][a-zA-Z]+$)","ParamsToIgnore":""},"Control":{"#comment":[],"TextControlId":"search","ButtonControlId":"search_button","CharCountThreshold":"3","ExternalCSS":"false","ExternalCSSUrl":""},"#comment":[],"EnableLoader":"false","InitialLoadOnly":"false","SearchResultMessage":null,"ProductResults":{"ShowRatings":"false","ProductRatingRatio":"5","#comment":[],"AddToCartType":"Magento","AddToCartBaseLink":"https://members.greendropship.com/checkout/cart/add/uenc/***window.url***/product/***product.mag_id***/","ProductNameLength":"60","Display":"List","#text":" -->\n    ","InfiniteScroll":"false","ResultRedirection":"0","NoImageUrl":"//ui.salespersonlab.com/UITemplateV6/Images/noimage.jpg","PriceSymbol":"$","PriceSymbolLeft":"1","PriceCall":"Click for Price"},"Toolbar":{/* EnableFirstLastPage - true/false - Enabling this option requires you to disable the BootProtectionEnabled setting for the client in the [Qwiser_System_Common].[dbo].[T_SITE_PARAMETERS] table*/"EnableFirstLastPage":"false","SortOptions":{"Relevancy":{"Default":"true","Alias":"Relevance","SingleOrder":"true","Ascending":"false","FieldName":"Relevancy"},"NameAtoZ":{"Alias":"Name: A-Z","SingleOrder":"true","Ascending":"true","FieldName":"Title"},"NameZtoA":{"Alias":"Name: Z-A","SingleOrder":"true","Ascending":"false","FieldName":"Title"},"PriceLtoH":{"Alias":"Price: Low to High","FieldName":"Price","SingleOrder":"true","Ascending":"true","Numeric":"true"},"PriceHtoL":{"Alias":"Price: High to Low","FieldName":"Price","Numeric":"true","SingleOrder":"true","Ascending":"false"}},"PageSizes":{/* Product count per page options */"Enabled":"true","Option":[{"Default":"true","Value":"12"},{"Value":"24"},{"Value":"36"}]}},"Refinements":{"InstantAnswerMode":"true","#comment":[],"SearchableAnswers":"true","SearchableAnswersThreshold":"20","RefinementVisibilityType":"1","RefinementVisibilityList":"[]","ShowMoreLess":"true","AnswerLimit":"5","EnablePriceSlider":"false","PriceSliderMin":"","PriceSliderMax":""},"Magento":{"RealTimePrice":"false","#comment":[],"RealTimePriceURL":"//celebshop.celebros.com/include_mage230ce/celinclude/"},"Language":{"AddToImportList":"Add to Import List","EditOnImportList":"Edit on Import List","Apply":"Apply","ClearAll":"Clear All","FilterBy":"Filter By:","Filters":"Filters","FirstPage":"First Page","Grid":"Grid","LastPage":"Last Page","Less":"- Less","List":"List","More":"+ More","Name":"Name","NextPage":"Next Page","Page":"Page","PreviousPage":"Previous Page","Price":"Price","Products":"Products","ProductsPerPage":"Show","RelatedSearches":"Related Searches","Relevancy":"Relevancy","Search":"Search:","ViewDetails":"View Details","RedirectMessage":"Are you looking for"}}};

        /* Convert Settings to boolean values */
        root.Settings.EnableLoader = root.Settings.EnableLoader !== undefined ? toBoolean(root.Settings.EnableLoader) : false; /* Default to false */
        root.Settings.Toolbar.EnableFirstLastPage = root.Settings.Toolbar.EnableFirstLastPage !== undefined ? toBoolean(root.Settings.Toolbar.EnableFirstLastPage) : false; /* Default to false */
        root.Settings.Toolbar.PageSizes.Enabled = root.Settings.Toolbar.PageSizes.Enabled !== undefined ? toBoolean(root.Settings.Toolbar.PageSizes.Enabled) : true; /* Default to true */
        root.Settings.General.InstantSearch = root.Settings.General.InstantSearch !== undefined ? toBoolean(root.Settings.General.InstantSearch) : false; /* Default to false */
        root.Settings.ProductResults.InfiniteScroll = root.Settings.ProductResults.InfiniteScroll !== undefined ? toBoolean(root.Settings.ProductResults.InfiniteScroll) : false; /* Default to false */
        root.Settings.ProductResults.ResultRedirection = root.Settings.ProductResults.ResultRedirection !== undefined ? toBoolean(root.Settings.ProductResults.ResultRedirection) : false; /* Default to false */
        root.Settings.ProductResults.ShowRatings = root.Settings.ProductResults.ShowRatings !== undefined ? toBoolean(root.Settings.ProductResults.ShowRatings) : false; /* Default to false */
        root.Settings.Refinements.InstantAnswerMode = root.Settings.Refinements.InstantAnswerMode !== undefined ? toBoolean(root.Settings.Refinements.InstantAnswerMode) : true; /* Default to true */
        root.Settings.Refinements.EnablePriceSlider = root.Settings.Refinements.EnablePriceSlider !== undefined ? toBoolean(root.Settings.Refinements.EnablePriceSlider) : false; /* Default to false */
        root.Settings.Refinements.SearchableAnswers = root.Settings.Refinements.SearchableAnswers !== undefined ? toBoolean(root.Settings.Refinements.SearchableAnswers) : false; /* Defaults to false */
        root.Settings.Refinements.ShowMoreLess = root.Settings.Refinements.ShowMoreLess !== undefined ? toBoolean(root.Settings.Refinements.ShowMoreLess) : true; /* Defaults to false */
        root.Settings.Control.ExternalCSS = root.Settings.Control.ExternalCSS !== undefined ? toBoolean(root.Settings.Control.ExternalCSS) : false; /* Default to false */
        root.Settings.EnableLoader = root.Settings.EnableLoader !== undefined ? toBoolean(root.Settings.EnableLoader) : false; /* Default to false */
        root.Settings.InitialLoadOnly = root.Settings.InitialLoadOnly !== undefined ? toBoolean(root.Settings.InitialLoadOnly) : false; /* Default to false */

        /* Backwards compatibility fix for ShowMoreLess/RefinementMoreLess */
        /* 0 = show more/less;
         * 1 = show all products, hide more/less */
        if (root.Settings.Refinements.RefinementMoreLess !== undefined) {
            if (root.Settings.Refinements.RefinementMoreLess === "1") {
                root.Settings.Refinements.ShowMoreLess = false;
            } else {
                root.Settings.Refinements.ShowMoreLess = true; /* Defaults to true */
            }
        }

        /* Numeric Values + defaults */
        root.Settings.Control.CharCountThreshold = root.Settings.Control.CharCountThreshold !== undefined ? parseInt(root.Settings.Control.CharCountThreshold) : 2; /* Default to 2 */
        root.Settings.Refinements.AnswerLimit = 6; /* Defaults to 5 */
        root.Settings.ProductResults.ProductRatingRatio = root.Settings.ProductResults.ProductRatingRatio !== undefined ? parseInt(root.Settings.ProductResults.ProductRatingRatio) : 10; /* Defaults to 10 */

        if (root.Settings.General.ParamsToIgnore === undefined) {
            root.Settings.General.ParamsToIgnore = root.Settings.General.DoSearchUrlParameter;
        }

        /* Magento Specific - Start */
        if (typeof root.Settings.Magento !== "undefined") {
            root.Settings.Magento.RealTimePrice = root.Settings.Magento.RealTimePrice !== undefined ? toBoolean(root.Settings.Magento.RealTimePrice) : false; /* Default to false */
        }
        /* Magento Specific - End */

        /* Global Search - Start */
        if (root.Settings.GlobalSearch === undefined) {
            root.Settings.GlobalSearch = {};
        }
        root.Settings.GlobalSearch.Enabled = root.Settings.GlobalSearch.Enabled !== undefined ? toBoolean(root.Settings.GlobalSearch.Enabled) : false; /* Default to false */

        /* The conditions below build out a list of elements that will be hidden when global search is enabled and a search is performed */
        root.Settings.GlobalSearch.ClientElements = null;
        if (root.Settings.GlobalSearch.Enabled) {
            try {
                var clientElements = [];
                if (root.Settings.GlobalSearch.MainContentID !== "") {
                    if (root.Settings.GlobalSearch.MainContentID.indexOf(",") >= 0) {
                        root.Settings.GlobalSearch.MainContentID = root.Settings.GlobalSearch.MainContentID.split(",");
                    } else {
                        root.Settings.GlobalSearch.MainContentID = [root.Settings.GlobalSearch.MainContentID];
                    }

                    /* This will grab all of the client defined elements based on the ID and add them to a list that we can reference later */
                    for (var i = 0; i < root.Settings.GlobalSearch.MainContentID.length; i++) {
                        var element = document.getElementById(root.Settings.GlobalSearch.MainContentID[i]);
                        clientElements = clientElements.concat(element);
                    }
                }

                if (root.Settings.GlobalSearch.MainContentClass !== "") {
                    if (root.Settings.GlobalSearch.MainContentClass.indexOf(",") >= 0) {
                        root.Settings.GlobalSearch.MainContentClass = root.Settings.GlobalSearch.MainContentClass.split(",");
                    } else {
                        root.Settings.GlobalSearch.MainContentClass = [root.Settings.GlobalSearch.MainContentClass];
                    }

                    /* This will grab all of the client defined elements based on the Class Names and add them to a list that we can reference later */
                    for (var i = 0; i < root.Settings.GlobalSearch.MainContentClass.length; i++) {
                        var element = Array.from(document.getElementsByClassName(root.Settings.GlobalSearch.MainContentClass[i]));
                        clientElements = clientElements.concat(element);
                    }
                }

                root.Settings.GlobalSearch.ClientElements = clientElements;
            } catch (e) {
                var urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get("debug") !== null) {
                    console.log("Global Search Configuration issues \r\n" + e);
                }
            }
        }
        /* Global Search - End */

        return root;
    });
angular
    .module("celebrosUI")
    .controller("CelController", ["$scope", "$http", "$location", "$timeout", "$window", "celAPI", "celConfig", "celSort", function ($scope, $http, $location, $timeout, $window, celAPI, celConfig, celSort) {
        $scope.settings = celConfig.Settings;
		$scope.mainScopeId = $scope.$id;
        if (!celConfig.Settings.Control.ExternalCSS) {
            LoadCSS();
        } else if (celConfig.Settings.Control.ExternalCSSUrl !== undefined && celConfig.Settings.Control.ExternalCSSUrl !== null && celConfig.Settings.Control.ExternalCSSUrl !== "") {
            LoadCSS(celConfig.Settings.Control.ExternalCSSUrl);
        }

        $scope.searchQuery = ""; /* This is the query from the searchBox */

        $scope.query = ""; /* This is the query from the search results */

        $scope.loading = false;
        if (celConfig.Settings.EnableLoader) {
            $scope.loading = true;
        }


        $scope.display = celConfig.Settings.ProductResults.Display.toLowerCase();
        $scope.pageSize = parseInt(celConfig.Settings.General.PageSize);

        /* Used when infinite scroll is enabled but a user copies and pastes a url containing a page to start from
           This will be set to true further down if Page=X is in the URL
         */
        $scope.showPagination = false;

        $scope.results = [];
        $scope.currentPage = -1;
        $scope.profile = "";
        $scope.sessionId = "";
        $scope.showSlider = false;
        $scope.slider = {};

        $scope.currentSort = celSort.GetCurrentSort();

        $scope.data = {
            siteId: celConfig.Settings.General.SiteKey,
            server: celConfig.Settings.General.SiteAddress,
            port: celConfig.Settings.General.SitePort,
            profile: celConfig.Settings.General.Profile,
            query: "",
            answerIds: [],
            pageSize: $scope.pageSize
        };

        $scope.dataDefault = JSON.parse(JSON.stringify($scope.data));

        $scope.pagination = [];
        $scope.toggleQuestions = false;
        $scope.returnFromProduct = false;
        $scope.showApply = false;
        $scope.firstLoad = true;
        $scope.priceLoaded = false;

        /* Magento specific variables */
        $scope.formKey = document.getElementsByName("form_key");
        if ($scope.formKey.length > 0) {
            $scope.formKey = $scope.formKey[0].value;
        }
        $scope.uenc = btoa(location.href);
        /* End Magento specific variables */

        $scope.specialKeys = [celConfig.Settings.General.DoSearchUrlParameter, 'debug', 'Page', 'Profile', 'PageSize', 'SortBy', 'Ascending', 'Display'];

        ignoredParams = celConfig.Settings.General.ParamsToIgnore.split(",");
        for (var k = 0; k < ignoredParams.length; k++) {
            $scope.specialKeys.push(ignoredParams[k]);
        }

        var searchTimer, i, j;

        $scope.postResults = null;

        var onResults = function (results) {
            if (results === undefined || results === null)
                return;

            /* Check Redirects */
            var redirect = null;
            if ($location.hash() === "redirect") {
                history.replaceState($scope.data, null, location.pathname + location.search);
                $scope.returnFromProduct = true;
            }
            var dpTypes = ["redirection url"];
            if (results.QueryConcepts.length > 0) {
                for (i = 0; i < results.QueryConcepts.length; i++) {
                    var concept = results.QueryConcepts[i];
                    for (j = 0; j < concept.DynamicProperties.length; j++) {
                        var dp = concept.DynamicProperties[j];
                        if (dpTypes.indexOf(dp.Name) >= 0) {
                            redirect = dp.Value;
                            break;
                        }
                    }
                }
            }
            /* We don't redirect if instant search is on, unless it's a search from a non-search page */
            if (redirect !== null) {
                var navType = -1;
                if (window.performance) {
                    navType = performance.navigation.type;
                }
                if (!$scope.returnFromProduct && (celConfig.Settings.General.InstantSearch === false || ($scope.firstLoad && navType !== 1))) {
                    //window.location.hash = "redirect";
                    $location.hash("redirect");
                    history.replaceState($scope.data, null, $location.absUrl());

                    window.location.href = redirect;
                    return;
                } else {
                    results.RecommendedMessage = celConfig.Settings.Language.RedirectMessage + ": <a href=" + redirect + ">" + redirect + "</a>";
                }
            }
            if ($scope.firstLoad) {
                $scope.firstLoad = false;
            }

            for (i = 0; i < results.Products.length; i++) {
                var prod = results.Products[i];
                var imported_ids = JSON.parse(window.localStorage.getItem('imported_ids'));
                prod.ImageUrl = 'https://m.gdss.us/media/catalog/product/cache/6af2da79007bbde83ac425b5e09ddcd4' + prod.ImageUrl.substr(98);
                var id = 0;
                results.Products[i].AddtionalFields.forEach(field => {
                    if(field.Name == 'mag_id'){
                        prod.ProductPageUrl = `search-products/${field.Value}`;
                        id = field.Value;
                    }

                });
                imported_ids.forEach(imported_id => {
                    if(imported_id == id){
                        prod.ProductPageUrl = 'import-list';
                    }
                });
                /* The spelling of Addtional fields is correct.. Someone misspelled it in the API and it was never fixed... */
                for (j = 0; j < prod.AddtionalFields.length; j++) {
                    var field = prod.AddtionalFields[j];
                    if (field.Name !== "SKU")
                        prod[field.Name] = field.Value;
                }
            }
            if (results.Products.length === 1 && !$scope.returnFromProduct && celConfig.Settings.ProductResults.ResultRedirection) {
                //window.location.hash = "redirect";
                $location.hash("redirect");
                history.replaceState($scope.data, null, $location.absUrl());
                window.location.href = results.Products[0].ProductPageUrl;
                return;
            }

            /* Reset showSlider to false */
            $scope.showSlider = false;
            results.Questions.splice(2, 1);
            if (celConfig.Settings.Refinements.EnablePriceSlider) {
                for (i = 0; i < results.Questions.length; i++) {
                    var question = results.Questions[i];
                    if (question.ID === "PriceQuestion") {
                        /* If the price question exists, set showSlider to true */
                        $scope.showSlider = true;
                        if (question.Answers.length > 0) {
                            //Price Question Answer IDs are formatted as follows...
                            //_Pmin_max
                            //Example: _P0_100
                            //This means that the first answer is the range $0 - $100, so we need to parse out the values so we can update the slider accordingly
                            var min = question.Answers[0].ID.replace("_P", "").split("_")[0];
                            var max = question.Answers[question.Answers.length - 1].ID.replace("_P", "").split("_")[1];

                            if (typeof $location.search()["Price"] !== 'undefined') {
                                min = $location.search()["Price"].replace("_P", "").split("_")[0];
                                max = $location.search()["Price"].replace("_P", "").split("_")[1];
                            }

                            min = parseFloat(min);
                            max = parseFloat(max);

                            $scope.slider.minValue = min;
                            $scope.slider.maxValue = max;
                            $scope.slider.options.floor = min;
                            $scope.slider.options.ceil = max;
                            if (celConfig.Settings.Refinements.PriceSliderMin !== "undefined" && celConfig.Settings.Refinements.PriceSliderMin !== "") {
                                var settingsMin = isNaN(parseInt(celConfig.Settings.Refinements.PriceSliderMin)) ? min : parseInt(celConfig.Settings.Refinements.PriceSliderMin);
                                $scope.slider.options.floor = settingsMin;

                                if (settingsMin > min) {
                                    $scope.slider.minValue = settingsMin;
                                }
                            }
                            if (celConfig.Settings.Refinements.PriceSliderMax !== "undefined" && celConfig.Settings.Refinements.PriceSliderMax !== "") {
                                var settingsMax = isNaN(parseInt(celConfig.Settings.Refinements.PriceSliderMax)) ? max : parseInt(celConfig.Settings.Refinements.PriceSliderMax);
                                $scope.slider.options.ceil = parseInt(celConfig.Settings.Refinements.PriceSliderMax);

                                if (settingsMax < max) {
                                    $scope.slider.maxValue = settingsMax;
                                }
                            }
                            /* This is needed to fix an issue with the min and max pointers rendering in the same position */
                            $timeout(function () {
                                $scope.$broadcast('reCalcViewDimensions');
                            }, 50);
                        }
                    }
                }
            }

            $scope.results = results;
            $scope.results.SearchPath = results.SearchPath[0];

            $scope.query = results.Query;
            $scope.currentPage = parseInt(results.CurrentPage);
            $scope.pageSize = parseInt(results.PageSize);
            $scope.profile = $scope.data.profile;
            $scope.display = getDisplay();
            $scope.sessionId = results.SessionId !== "" ? results.SessionId : $scope.SessionId;

            /* Updated $scope.data */
            $scope.data.searchHandle = results.Handle;
            $scope.data.page = $scope.currentPage;
            $scope.data.pageSize = $scope.pageSize;

            $scope.selectedAnswers = [];
            for (i = 0; i < $scope.results.SearchPath.Answers.length; i++) {
                $scope.selectedAnswers.push($scope.results.SearchPath.Answers[i].ID);
            }

            if ($scope.currentPage > 0) {
                $scope.showPagination = true;
            } else {
                $scope.showPagination = false;
            }
            if (typeof celConfig.Settings.Magento !== "undefined") {
                if (celConfig.Settings.Magento.RealTimePrice === true) {
                    var skus = $scope.results.Products.map(function (product) {
                        return product.Sku;
                    });
                    celAPI.getPrice(skus).then(onGetPriceSuccess, onGetPriceError);
                }
            }

            $scope.returnFromProduct = false;

            $window.scrollTo(0, 0);

            if ($scope.postResults !== null) {
                $scope.postResults();
                $scope.postResults = null;
            }

            if (celConfig.Settings.EnableLoader) {
                $scope.loading = false;
                if (celConfig.Settings.InitialLoadOnly) {
                    celConfig.Settings.EnableLoader = false;
                }
            }
        };

        var onError = function (reason) {
            if (celConfig.Settings.EnableLoader) {
                $scope.loading = false;
            }
        };

        var onGetPriceSuccess = function (data) {
            for (var i = 0; i < $scope.results.Products.length; i++) {
                var sku = $scope.results.Products[i].Sku;
                $scope.results.Products[i].RealTimePrices = data[sku];
                if (parseInt(data[sku].regular_price) !== 0 && data[sku].regular_price < $scope.results.Products[i].Price) {
                    $scope.results.Products[i].OldPrice = $scope.results.Products[i].Price;
                    $scope.results.Products[i].Price = data[sku].regular_price;
                }
            }
            $scope.priceLoaded = true;
        };

        var onGetPriceError = function (reason) {
            console.log(reason);
        };

        $scope.onResults = onResults;
        $scope.onError = onError;

        /* This will hide the keyboard on mobile devices when a user starts scrolling */
        var ua = navigator.userAgent;
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Mobile|mobile|CriOS/i.test(ua)) {
            angular.element($window).bind("scroll", function () {
                if (angular.element($window)[0].pageYOffset > 175) {
                    document.getElementById(celConfig.Settings.Control.TextControlId).blur();
                }
            });
        }

        var getDisplay = function () {
            var result = celConfig.Settings.ProductResults.Display;
            if ($location.search()["Display"] !== undefined) {
                result = $location.search()["Display"];
            } else if ($scope.profile !== null && $scope.profile !== "") {
                var profileTabs = celConfig.Settings.General.ProfileTabs;
                if (profileTabs !== undefined && profileTabs !== null) {
                    /* This gets the Display type (grid or list) for the current profile */
                    if (Object.keys(profileTabs).length > 0) {
                        for (var profile in profileTabs) {
                            if (profileTabs[profile].Profile === $scope.profile) {
                                if (profileTabs[profile].Display !== undefined) {
                                    result = profileTabs[profile].Display;
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            return result.toLowerCase();
        };



        $scope.clearAll = function () {
            if (celConfig.Settings.EnableLoader) {
                $scope.loading = true;
            }

            $scope.data.answerId = null;
            if ($scope.data.answerIds !== undefined) {
                $scope.data.answerIds = [];
            }
            if ($scope.data.page !== undefined) {
                delete $scope.data.page;
            }

            if ($scope.data.fieldName === "Relevancy") {
                delete $scope.data.fieldName;
                if ($scope.data.isAscending !== undefined) {
                    delete $scope.data.isAscending;
                }
                if ($scope.data.isNumericSort !== undefined) {
                    delete $scope.data.isNumericSort;
                }
            }

            celAPI.doSearchParams($scope.data).then(onResults, onError);

            for (key in $location.search()) {
                if ($scope.specialKeys.indexOf(key) < 0 || key === "Page") {
                    $location.search(key, null);
                }
            }
            history.pushState($scope.data, null, $location.absUrl());
        };

        /* Back/Forward Handling
         * Watch for location changes so we can apply state accordingly
         */
        $scope.$on('$locationChangeSuccess', function (a, newUrl, oldUrl) {
            if (oldUrl !== newUrl && oldUrl.indexOf("#redirect") < 0) {
                if ($location.state() !== null) {

                    $scope.data = $location.state();

                    var data = JSON.parse(JSON.stringify($scope.data));

                    $scope.currentSort.alias = $location.search()["SortBy"] !== undefined ? $location.search()["SortBy"] : "Relevancy";
                    $scope.currentSort.fieldName = data.fieldName;
                    $scope.currentSort.isAscending = toBoolean(data.isAscending);
                    $scope.currentSort.isNumericSort = toBoolean(data.isNumericSort);

                    if ($location.search()["Profile"] !== undefined) {
                        localStorage.setItem("celUIT_profile", $location.search()["Profile"]);
                    }

                    $scope.display = getDisplay();

                    if (data !== "") {

                        //Set up data to send to API DoSearchParams function
                        data.effectOnSearchPath = 1;
                        if (data.answerIds !== undefined) {
                            data.answerIds = $scope.data.answerIds.toString();
                        }

                        celAPI.doSearchParams(data).then(onResults, onError);
                    }

                }
                /*else {
                	$window.location.reload();
                }*/
                $scope.url = $location.absUrl();
            }
        });

        /* request is used for the instant search */
        var request;
        $scope.searchPage = false; /* This is used Instant Search and for Nav to Search below */
        $scope.$watch("searchQuery", function (newQuery, oldQuery) {
            $timeout.cancel(searchTimer);
            if (request !== undefined) {
                request.cancel();
            }
            if (newQuery !== oldQuery && $scope.searchPage) {
                searchTimer = $timeout($scope.search, 130);
            }
        });

        /* Regular Search function handling */
        $scope.search = function () {
            if (request !== undefined && request !== null) {
                request.cancel();
            }

            if ($scope.searchQuery !== $scope.query) {
                $scope.query = $scope.searchQuery;
            }
            //$scope.query = escapeHtml($scope.query);
            $scope.data.query = $scope.query;

            request = celAPI.doSearch($scope.data);
            request.promise.then(onResults, onError);

            for (key in $location.search()) {
                if ($scope.specialKeys.indexOf(key) < 0 || key === "Page") {
                    $location.search(key, null);
                }
            }

            $location.search("q", $scope.query);
            history.pushState($scope.data, null, $location.absUrl());
        };

        $scope.addToImportList = function (event) {
            var data = {
                'action': 'add_import_list',
                'id_product': event.target.id.split('-')[1]
            }
            document.getElementById(`check-${event.target.id.split('-')[1]}`).disabled = true;
            $http({
                url: '/ajax',
                method: 'GET',
                params: data
            }).then(res=> {
                var new_ids = JSON.parse(window.localStorage.getItem('imported_ids'));
                new_ids.push(JSON.parse(res.data));
                window.localStorage.setItem('imported_ids', JSON.stringify(new_ids));
                document.getElementById(`add-${JSON.parse(res.data)}`).hidden = true;
                document.getElementById(`${JSON.parse(res.data)}`).href = 'import-list';
                document.getElementById(`edit-${JSON.parse(res.data)}`).hidden = false;
                document.getElementById(`check-${JSON.parse(res.data)}`).checked = false;
            })
        };

        $scope.checkAll = function (event) {
            var checkboxes = document.querySelectorAll('.check-product');
            checkboxes.forEach(checkbox => {
                if (event.target.checked){
                    if(!checkbox.disabled)
                        checkbox.checked = true;
                }
                else checkbox.checked = false;
            });
        }

        $scope.allAddToImportList = function (event) {
            var product_ids = [];
            var checkboxes = document.querySelectorAll('.check-product');
            checkboxes.forEach(checkbox => {
                if (checkbox.checked){
                    product_ids.push(checkbox.id.split('-')[1]);
                    checkbox.disabled = true;
                }
            });
            var data = {
                'product_ids': product_ids
            }
            if (product_ids.length) {
                event.target.disabled = true;
                $http.post('/ajax',{product_ids: product_ids})
                    .then(res=> {
                        event.target.disabled = false;
                        var imported_ids = JSON.parse(window.localStorage.getItem('imported_ids'));
                        var new_ids = [];
                        new_ids = imported_ids.concat(res.data);
                        window.localStorage.setItem('imported_ids', JSON.stringify(new_ids));
                        res.data.forEach(id => {
                            document.getElementById(`add-${JSON.parse(id)}`).hidden = true;
                            document.getElementById(`${JSON.parse(id)}`).href = 'import-list';
                            document.getElementById(`edit-${JSON.parse(id)}`).hidden = false;
                            document.getElementById(`check-${JSON.parse(id)}`).checked = false;
                    });
                })

            }
            else {
                alert('At least one checkbox must be selected');
            }
        };
        $scope.editonimportlist = function (event) {
            window.location.href = "/import-list";
        }
        $scope.searchProducts = function (event) {
            var flag = true;
            if (event) {
                if (event.keyCode != 13) {
                    flag = false;
                }
            }
            var search_key = document.querySelector('#search-products').value;
            var wcfServer = celConfig.Settings.General.WcfAddress;
            var data = {
                query: search_key,
                siteId: celConfig.Settings.General.SiteKey,
                searchHandle: $scope.data.searchHandle,
                pageSize: $scope.pageSize,
                principles: true
            }
            if (flag) {
                celAPI.Search(data).promise.then(onResults, onError);
            }
        }
        /*
         *
         * Everything below this is only used for the page load
         *
         * */

        /* This is done in case any clients have different UI's on the same domain (via subdomain or something similar) */
        var lsKey = localStorage.getItem("celUIT_key");
        if (lsKey !== null && lsKey !== celConfig.Settings.General.SiteKey) {
            localStorage.removeItem("celUIT_profile");
            localStorage.removeItem("celUIT_display");
        }

        localStorage.setItem("celUIT_key", celConfig.Settings.General.SiteKey);

        /* QueryString Parameters */
        var searchString = new URLSearchParams(location.search);
        if (searchString.get("q") !== null){
        	$scope.data.query = $scope.searchQuery = searchString.get("q");
        	$scope.searchPage = true;
        } else if ($location.search()[celConfig.Settings.General.DoSearchUrlParameter] !== undefined && $location.search()[celConfig.Settings.General.DoSearchUrlParameter].length > 0
        	|| (location.href.indexOf("uitemplate") > -1
        	|| location.href.indexOf("localhost") > -1)) {
            $scope.data.query = $scope.searchQuery = $location.search()[celConfig.Settings.General.DoSearchUrlParameter];
            $scope.searchPage = true;
        }

        /* Nav to Search Regex*/
         if (celConfig.Settings.General.UrlRegexPattern !== undefined && !$scope.searchPage) {
            var pattern = new RegExp(celConfig.Settings.General.UrlRegexPattern, "gi");
             var query = location.pathname.match(pattern);
             if (query !== null && query.length > 0) {
                 query = query[0].replace(/[-\/]/g, " ");
                 $scope.data.query = $scope.searchQuery = query;
             }
         }

        if(!$scope.searchPage || location.href.indexOf("uitemplate") > -1) {
        	var categoryTerm = document.getElementsByClassName("uk-breadcrumb");
        	if(categoryTerm !== null && categoryTerm.length > 0) {
        		$scope.data.query = categoryTerm[0].innerText.replace(/(Home)|(\n)|( > )/ig, " ").trim();//.replace("Home\n", "").replace(" > ", " ").trim();
        	}
        }

        if ($location.search()["Display"] !== undefined) {
            $scope.display = $location.search()["Display"];
        }

        var params = false;
        if ($location.search()["Page"] !== undefined) {
            $scope.data.page = parseInt($location.search()["Page"]) - 1;
            if ($scope.data.page > 0) {
                $scope.showPagination = true;
            }
            params = true;
        }

        if ($location.search()["PageSize"] !== undefined) {
            $scope.data.pageSize = $scope.pageSize = parseInt($location.search()["PageSize"]);

            params = true;
        }

        $scope.data.fieldName = $scope.currentSort.fieldName;
        $scope.data.isAscending = $scope.currentSort.isAscending;
        $scope.data.isNumericSort = $scope.currentSort.isNumericSort;


        if ($location.search()["Profile"] !== undefined || localStorage.getItem("celUIT_profile") !== null) {
            name = $location.search()["Profile"];
            if (name === null || name === "undefined") {
                name = localStorage.getItem("celUIT_profile");
                $location.search("Profile", name);
            }

            var profileTabs = celConfig.Settings.General.ProfileTabs;
            if (profileTabs !== null && profileTabs !== undefined && Object.keys(profileTabs).length > 0) {
                var profile = celConfig.Settings.General.ProfileTabs[name];
                $scope.data.profile = profile.Profile;
                $scope.display = profile.Display !== undefined ? profile.Display : celConfig.Settings.ProductResults.Display.toLowerCase();
            } else {
                $scope.data.profile = name;
            }
            params = true;

            localStorage.setItem("celUIT_profile", name);
        }

        for (key in $location.search()) {
            if ($scope.specialKeys.indexOf(key) < 0) {
                // This will check if the ids are all numeric, and will ignore values that are not.
                if ($location.search()[key].indexOf($location.search()[key].match(/^[\d_P]+,*\d/)) > -1) {
                    var split = $location.search()[key].split(",");
                    $scope.data.answerIds = $scope.data.answerIds.concat(split);
                    params = true;
                }
            }
        }

        if ($scope.searchQuery === undefined || $scope.searchQuery === "") {
        	$scope.data.profile = celConfig.Settings.General.EmptyQueryProfile;
        	params = true;
        } else {
        	$scope.data.profile = "";
        }

        if ($scope.searchPage) {
            if (!$scope.returnFromProduct) {
                history.replaceState($scope.data, null, $location.absUrl());
            }

            if (celConfig.Settings.General.InstantSearch) {
                instantSearch(celConfig.Settings.Control.TextControlId, celConfig.Settings.Control.CharCountThreshold);
            }
        }
        $window.scrollTo(0, 0);

        if (!params) {
            celAPI.doSearch($scope.data).promise.then(onResults, onError);
        } else {
            $scope.data.effectOnSearchPath = 1;
            celAPI.doSearchParams($scope.data).then(onResults, onError);
        }
    }]);
angular
    .module("celebrosUI")
    .directive("celDisplay", ['$location', function ($location) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                var changeDisplay = function (display) {
                    /* scope.$apply is needed because the data isn't changing (and therefore not triggering an update)
                     * and this needs to get processed immediately */
                    var newDisplay = display.attributes["cel-display"].value;
                    if (newDisplay !== scope.display) {
                        scope.$apply(function () {
                            scope.display = display.attributes["cel-display"].value;

                            $location.search("Display", display.attributes["cel-display"].value);
                            history.pushState(scope.data, null, $location.absUrl());
                        });
                    }
                };
                elem.bind("click", function () {
                    changeDisplay(this);
                });

                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        changeDisplay(this);
                    }
                });
            }
        };
    }]);
/* Start celHelpers.js */
angular.module("celebrosUI")
    .directive("onError", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.on("error", function () {
                    elem.attr("src", attr.onError);
                });
            }
        };
    });

angular
    .module("celebrosUI")
    .directive("toggleId", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                scope.toggle = false;
                elem.bind("click", function () {
                    scope.$apply(function () {
                        var toggleElem = document.getElementById(attr.toggleId);
                        var classes = toggleElem.attributes["toggle-class"].split(" ");
                        scope.toggle = !scope.toggle;
                        var i;
                        if (scope.toggle) {
                            for (i = 0; i < classes.length; i++) {
                                toggleElem.classList.add(classes[i]);
                            }
                        } else {
                            for (i = 0; i < classes.length; i++) {
                                toggleElem.classList.remove(classes[i]);
                            }
                        }
                    });
                });
            }
        };
    });

angular
    .module("celebrosUI")
    .directive("celToggle", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.bind("click", function (event) {
                    event.stopPropagation();
                    scope.$apply(function () {
                        scope.toggleQuestions = !scope.toggleQuestions;
                    });
                });

                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        event.stopPropagation();
                        scope.$apply(function () {
                            scope.toggleQuestions = !scope.toggleQuestions;
                        });
                    }
                });
            }
        };
    });

angular
    .module("celebrosUI")
    .directive("celFilterApply", ['celAPI', function (celAPI) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.bind("click", function (event) {
                    event.stopPropagation();
                    scope.showApply = false;
                    celAPI.doSearchParams(scope.data).then(scope.onResults, scope.onError);
                });
            }
        };
    }]);

angular
    .module("celebrosUI")
    .directive("celToTop", ['$window', function ($window) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.bind("click", function () {
                    $window.scrollTo(0, 0);
                });
                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        $window.scrollTo(0, 0);
                    }
                });
            }
        };
    }]);

angular
    .module('celebrosUI')
    .filter('trust_html', ['$sce', function ($sce) {
        return function (text) {
            return $sce.trustAsHtml(text);
        };
    }]);

angular
    .module('celebrosUI')
    .filter('format_price', [function () {
        return function (price) {
            if (!isNaN(price)) {/* Check if the price is actually a number */
               price = parseFloat(Math.round(price * 100) / 100).toFixed(2); /* Round it to the nearest 2 decimal places */
            }
            return price;
        };
    }]);

angular
    .module('celebrosUI')
    .filter('currency_symbol', ["celConfig", function (celConfig) {
        return function (price) {
            if (celConfig.Settings.ProductResults.PriceSymbolLeft === "1") {
                price = celConfig.Settings.ProductResults.PriceSymbol + price;
            } else {
                price = price + celConfig.Settings.ProductResults.PriceSymbol;
            }
            return price;
        };
    }]);
/* End celHelpers.js */
angular
    .module("celebrosUI")
    .directive("celInfiniteScroll", [ '$window', '$location', 'celConfig', 'celAPI', function ($window, $location, celConfig, celAPI) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                if (celConfig.Settings.ProductResults.InfiniteScroll) {
                    scope.showToTop = false;
                    var raw = angular.element($window)[0];
                    var scrolling = false;

                    var getDocHeight = function () {
                        return Math.max(
                            document.body.scrollHeight, document.documentElement.scrollHeight,
                            document.body.offsetHeight, document.documentElement.offsetHeight,
                            document.body.clientHeight, document.documentElement.clientHeight
                        );
                    };

                    var onInfiniteResults = function (results) {
                        scope.currentPage = parseInt(results.CurrentPage);
                        for (var i = 0; i < results.Products.length; i++) {
                        	var prod = results.Products[i];
                        	 for (j = 0; j < prod.AddtionalFields.length; j++) {
			                    var field = prod.AddtionalFields[j];
			                    if (field.Name !== "SKU"){
			                        prod[field.Name] = field.Value;

			                    }
			                }
                            scope.results.Products.push(results.Products[i]);
                        }
                        scrolling = false;


                    };

                    var infiniteScroll = function () {
                        if (scope.currentPage + 1 < parseInt(scope.results.PagesCount)) {
                            scope.data.pageNumber = scope.currentPage + 1;

                            celAPI.doMovePage(scope.data).then(onInfiniteResults, scope.onError);

                            /* Pages start from 0 in the API so we need to add 1 to the URL */
                            $location.search("Page", scope.data.pageNumber + 1);
                            //history.pushState(scope.data, null, $location.absUrl());
                        }
                    };


                    angular.element($window).bind("scroll", function () {
                        if (raw.pageYOffset > raw.innerHeight / 2) {
                            scope.$apply(function () {
                                scope.showToTop = true;
                            });
                        } else {
                            scope.$apply(function () {
                                scope.showToTop = false;
                            });
                        }
                        if (raw.pageYOffset + raw.innerHeight >= getDocHeight() - 400 && !scrolling) {
                            scope.$apply(function () {
                                scrolling = true;
                                infiniteScroll();
                            });
                        }
                    });

                    scope.$watch("query", function () {
                        scrolling = false;
                    });

                    scope.$watch("selectedAnswers", function () {
                        scrolling = false;
                    });
                }
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celPage", ['$location', 'celAPI', 'celConfig', function ($location, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            /*templateUrl: "partials/page.html",*/
            link: function (scope, elem, attr) {

                changePage = function (p) {
                    if (p.className !== undefined) {
                        if (p.className.indexOf("selected") === -1) {
                            /* This is a hacked way of getting to the celController scope so we can modify the same scope.loading object */
                            var parent = scope;
                            var parentId = scope.$id;
                            while (parent.$parent && parent.$id != scope.mainScopeId) {
                                parent = parent.$parent;
                                parentId = parent.$id;
                            }
                            if (celConfig.Settings.EnableLoader) {
                                parent.loading = true;
                            }

                            parent.data.page = p.attributes["page"].value;

                            /* Create a new data object because doMovePage needs 'pageNumber' instead of 'page' */
                            var data = JSON.parse(JSON.stringify(parent.data));
                            data.pageNumber = p.attributes["page"].value;

                            celAPI.doMovePage(data).then(parent.onResults, parent.onError);

                            $location.search("Page", parseInt(p.attributes["page"].value) + 1);
                            history.pushState(parent.data, null, $location.absUrl());
                        }
                    }
                };

                elem.bind("click", function () {
                    changePage(this);
                });
                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        changePage(this);
                        event.preventDefault();
                    }
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celPageSizer", ['$location', 'celAPI', function ($location, celAPI) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.bind("change", function (event) {
                    var size = event.target.value;
                    if (scope.pageSize === size) {
                        return;
                    }
                    scope.$apply(function () {
                        scope.data.pageSize = size;
                        celAPI.doChangeProductsPerPage(scope.data).then(scope.onResults, scope.onError);

                        $location.search("PageSize", size);
                        $location.search("Page", null);
                        history.pushState(scope.data, null, $location.absUrl());
                    });
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celPagination", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<ul class=\"flex-box-row\">    <li cel-page ng-hide=\"currentPage == 0 || !settings.Toolbar.EnableFirstLastPage\" page=\"{{ 0 }}\" class=\"firstPage\" tabindex=\"0\" title=\"{{ settings.Language.FirstPage }}\"><span class=\"cel-icon-angle-double-left\"></span></li>    <li cel-page ng-hide=\"currentPage == 0\" page=\"{{ currentPage - 1 }}\" class=\"prevPage\" tabindex=\"0\" title=\"{{ settings.Language.PreviousPage }}\"><span class=\"cel-icon-angle-left\"></span></li>    <li cel-page ng-repeat=\"pageNum in pagination\" page=\"{{ pageNum }}\" ng-class=\"{ selected : pageNum == (currentPage) }\" ng-attr-tabindex=\"{{pageNum == (currentPage) ? '': '0'}}\" title=\"{{ settings.Language.Page }} {{pageNum + 1}}\"><span>{{ pageNum + 1}}</span></li>    <li cel-page ng-hide=\"(currentPage + 1) == results.PagesCount\" page=\"{{ currentPage + 1 }}\" class=\"nextPage\" tabindex=\"0\" title=\"{{ settings.Language.NextPage }}\"><span class=\"cel-icon-angle-right\"></span></li>    <li cel-page ng-hide=\"(currentPage + 1) == results.PagesCount || !settings.Toolbar.EnableFirstLastPage\" page=\"{{ results.PagesCount - 1 }}\" class=\"lastPage\" tabindex=\"0\" title=\"{{ settings.Language.LastPage }}\"><span class=\"cel-icon-angle-double-right\"></span></li></ul>",
            link: function (scope, elem, attr) {

                var pagination = function () {
                    /* This clears the array without creating a new one entirely.
                     * It preserves the reference so angular is still bound to its values
                     */
                    scope.pagination.length = 0;

                    var curPage = parseInt(attr.currentPage);

                    var size = 5;
                    var startPage = curPage - Math.floor(size / 2);
                    var endPage = curPage + Math.floor(size / 2);
                    var totalPages = parseInt(scope.results.PagesCount);

                    if (startPage <= 0) {
                        endPage -= startPage;
                        startPage = 0;
                    }

                    if (endPage > totalPages) {
                        endPage = totalPages;
                        if (endPage - size + 1 > 1) {
                            startPage = endPage - size + 1;

                        } else {
                            startPage = 0;
                        }
                    }
                    for (var i = startPage; i < endPage; i++) {
                        scope.pagination.push(i);
                    }
                };

                attr.$observe("currentPage", function (newValue, oldValue) {
                    //This gets called when data changes.

                    pagination();
                });

                attr.$observe("pagesCount", function (newValue, oldValue) {
                    //This gets called when data changes.

                    pagination();
                });
            }
        };
    });
angular
    .module("celebrosUI")
    .directive("celPriceSlider", ["$location", "$timeout", "celAPI", "celConfig", function ($location, $timeout, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            /* templateUrl: "partials/price-slider.html",*/
            link: function (scope, elem, attr) {
                /* This is a hacked way of getting to the celController scope */
                var parent = scope;
                var parentId = scope.$id;
                while (parent.$parent && parent.$id != scope.mainScopeId) {
                    parent = parent.$parent;
                    parentId = parent.$id;
                }

                var minVal = 0;
                var maxVal = 999999;
                var floorVal = -1;
                var ceilVal = -1;

                if (typeof celConfig.Settings.Refinements.PriceSliderMin !== "undefined" && celConfig.Settings.Refinements.PriceSliderMin !== null && celConfig.Settings.Refinements.PriceSliderMin !== "") {
                    floorVal = celConfig.Settings.Refinements.PriceSliderMin;
                }
                if (typeof celConfig.Settings.Refinements.PriceSliderMax !== "undefined" && celConfig.Settings.Refinements.PriceSliderMax !== null && celConfig.Settings.Refinements.PriceSliderMax !== "") {
                    ceilVal = celConfig.Settings.Refinements.PriceSliderMax;
                }

                parent.slider = {
                    minValue: minVal,
                    maxValue: maxVal,
                    options: {
                        floor: floorVal !== -1 ? floorVal : minVal,
                        ceil: ceilVal !== -1 ? ceilVal : maxVal,
                        step: 1,
                        translate: function (value, sliderId, label) {

            			const options2 = { style: 'currency', currency: 'USD' };
						const numberFormat2 = new Intl.NumberFormat('en-US', options2);
                            return   numberFormat2.format(value);
                        },
                        onEnd: function (sliderId, modelValue, highValue, pointerType) {
                            if (modelValue === parent.slider.options.floor && highValue === parent.slider.options.ceil)
                                return;
                            for (var i = 0; i < parent.results.SearchPath.Answers.length; i++) {
                                var answer = parent.results.SearchPath.Answers[i];
                                if (parent.results.SearchPath.Answers[i].QuestionId === "PriceQuestion") {
                                    parent.data.answerIds = parent.data.answerIds.filter(function (val) { return val !== parent.results.SearchPath.Answers[i].ID; });
                                }
                            }
                            parent.data.answerIds.push("_P" + modelValue + "_" + highValue);

                            $location.search("Price", "_P" + modelValue + "_" + highValue);

                            celAPI.doSearchParams(parent.data).then(parent.onResults, parent.onError);
                            history.pushState(parent.data, null, $location.absUrl());
                        }
                    }
                };

                scope.$watch("toggleQuestions", function () {
                    /* This is needed to fix an issue with the min and max pointers rendering in the same position */
                    $timeout(function () {
                        parent.$broadcast('reCalcViewDimensions');
                    }, 50);
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celProdAnlx", ['celAnlx', 'celConfig', function (celAnlx, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                var cid = celConfig.Settings.General.AnalyticsCustomerName !== "" ? celConfig.Settings.General.AnalyticsCustomerName : celConfig.Settings.General.SiteKey;

                elem.bind("click", function () {
                    var data = {
                        type: "PD",
                        responseType: "JSON",
                        cid: cid,
                        src: window.location.toString(),
                        ref: document.referrer,
                        ssid: scope.sessionId,
                        wsid: "",
                        sku: scope.product.Sku,
                        name: scope.product.Name,
                        price: scope.product.Price,
                        lh: scope.results.LogHandle
                    };
                    celAnlx.sendAnlx(data);
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celProduct", ['celConfig', function (celConfig) {
        var imported_ids = JSON.parse(window.localStorage.getItem('imported_ids'));

        return {
            restrict: "A",
            template: "<div class=\"cuit_item_container\" ng-class=\"{ 'flex-box-column' : display == 'grid', 'flex-box-row' : display == 'list' }\"><input type=\"checkbox\" id=\"check-{{product.mag_id}}\" class=\"check-product\"><a cel-prod-anlx id=\"{{product.mag_id}}\" class=\"product-details\" href=\"{{ product.ProductPageUrl }}\" target=\"_blank\">        <div class=\"product-image\"><img ng-src=\"{{product.ImageUrl}}\" on-error=\"{{ settings.ProductResults.NoImageUrl }}\" max-width=\"240px\" max-height=\"300px\" />        </div>               <div class=\"product-info\">            <div class=\"product-title\" ng-bind-html=\"product.Name | trust_html\"></div>            <div class=\"product-sku\">SKU #{{ product.Sku }}</div>             <div ng-if=\"product.is_in_stock==0\" class=\"product-stock\">{{ product.is_in_stock.replace('0','OUT OF STOCK') }}</div>                        <div ng-show=\"display == 'list'\" class=\"product-description hidden-sm\" ng-bind-html=\"product.Description | trust_html\">            </div>        </div>                 </a>    <div class=\"product-shop\" ng-if=\"product.Price !== '0'\">	    	 <div ng-if=\"settings.ProductResults.ShowRatings === true\" class=\"ratings-container\">	            <div class=\"ratings\">	                <span class=\"ratings-stars ratings-stars-off\">â˜…â˜…â˜…â˜…â˜…</span>	                <span class=\"ratings-stars ratings-stars-on\" ng-style=\"{ width: product.RatingStars + '%' }\">â˜…â˜…â˜…â˜…â˜…</span>	            </div>	        </div>    		 <div ng-show=\"product.Original_Price !== undefined && product.Original_Price !== product.Price && product.Original_Price !== '0.00'\" class=\"old-product-price\">{{ product.Original_Price | currency : \"$\" : 2  }}</div>	        <div ng-hide=\"settings.Magento.RealTimePrice === true && !priceLoaded\" class=\"product-price\">{{ product.Price | currency : \"$\" : 2 }}</div>	                 		</div> <!--End addtocard vtex -->		     <div class=\"add-to-cart\">		            <a ng-if=\"product.AddToCart === undefined\" cel-prod-anlx href=\"{{ product.ProductPageUrl }}\" target=\"_self\">{{ settings.Language.ViewDetails }}</a>		            <form ng-if=\"product.AddToCart !== undefined && formKey !== undefined\" data-role=\"tocart-form\">		                <input type=\"hidden\" name=\"product\" value=\"{{ product.mag_id }}\" />		                <input type=\"hidden\" name=\"uenc\" value=\"{{ uenc }}\" />		                <input type=\"hidden\" name=\"form_key\" value=\"{{ formKey }}\" />		                </form><div class=\"control\"><button cel-prod-anlx id=\"add-{{product.mag_id}}\" ng-click=\"addToImportList($event)\" type=\"submit\" title=\"{{ settings.Language.AddToImportList }}\" class=\"btn-default\">{{ settings.Language.AddToImportList }}</button><button cel-prod-anlx id=\"edit-{{product.mag_id}}\" hidden ng-click=\"editonimportlist($event)\" type=\"submit\" title=\"{{ settings.Language.EditOnImportList }}\" class=\"btn-default edit\">{{ settings.Language.EditOnImportList }}</button></div></div></div></div>",
            link: function (scope, elem, attr) {
                imported_ids.forEach(imported_id => {
                    if(imported_id == scope.product.mag_id){
                        elem[0].children[0].children[2].children[0].children[0].hidden = true;
                        elem[0].children[0].children[2].children[0].children[1].hidden = false;
                        elem[0].children[0].children[0].disabled = true;
                    }
                });
                if (celConfig.Settings.ProductResults.ShowRatings) {
                    scope.product.Rating = scope.product.Rating !== undefined && scope.product.Rating !== '' ? parseFloat(scope.product.Rating) : 0; /* Defaults to 0 */
                    scope.product.RatingStars = (scope.product.Rating / celConfig.Settings.ProductResults.ProductRatingRatio) * 100;
                }

                if (celConfig.Settings.ProductResults.AddToCartType !== "Default") {
                    scope.product.AddToCart = celConfig.Settings.ProductResults.AddToCartBaseLink;
                    if (scope.product.AddToCart !== null) {
                        scope.product.AddToCart = scope.product.AddToCart;
                    }
                }
            }
        };
    }]);


angular
    .module("celebrosUI")
    .directive("celProfile", ['$location', 'celAPI', 'celConfig', function ($location, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                elem.bind("click", function () {
                    if (celConfig.Settings.EnableLoader) {
                        scope.$parent.loading = true;
                    }

                    scope.$parent.data.profile = attr.profile;

                    scope.$parent.data.searchHandle = "";
                    scope.$parent.data.answerIds = [];


                    if (attr.display === "") {
                        attr.display = celConfig.Settings.ProductResults.Display;
                    }

                    attr.display = attr.display.toLowerCase();

                    celAPI.doSearchParams(scope.$parent.data).then(scope.$parent.onResults, scope.$parent.onError);

                    for (key in $location.search()) {
                        if (scope.specialKeys.indexOf(key) < 0 || key === "Page") {
                            $location.search(key, null);
                        }
                    }

                    $location.search("Page", null);
                    $location.search("Display", null);
                    $location.search("Profile", scope.name);
                    localStorage.setItem("celUIT_profile", scope.name);
                    history.pushState(scope.$parent.data, null, $location.absUrl());
                });
            }
        };
    }]);

angular
    .module("celebrosUI")
    .directive("celQuestion", ['celConfig', function (celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<div id=\"title-{{ question.Name }}\" class=\"question-title\" ng-class=\"{'answers-hidden cel-icon-plus' : question.toggle === true, 'cel-icon-minus' : question.toggle === false }\"><span style=\"margin-left:10px;\">{{ question.SideText }}</span></div><input id=\"search-{{ question.Name }}\" class=\"form-control answer-search\" type=\"text\" placeholder=\"Search {{ question.SideText }}\" ng-show=\"question.SearchableAnswers && question.AllAnswers.length > question.SearchableAnswersThreshold && !question.toggle\" ng-model=\"question.Search.Text\" ng-click=\"$event.stopPropagation();\"/><ul class=\"answers\" ng-hide=\"question.toggle\" ng-class=\"{ 'no-check' : question.IsHierarchical == true, 'disabled' : question.answerSelected != '' }\">    <li cel-answer ng-repeat=\"answer in (question.AnswersDisplayed = (question.AllAnswers | filter: question.Search | limitTo: question.AnswerLimit))\" id=\"{{ answer.Text }}\" class=\"answer truncate\" ng-class=\"{ selected : selectedAnswers.indexOf(answer.ID) > -1, 'disabled' : !question.enabled && selectedAnswers.indexOf(answer.ID) < 0 }\" title=\"{{ answer.Text }}\" answer-id=\"{{ answer.ID }}\" tabindex=\"0\">        <!-- Template in answer.html-->    </li></ul><span class=\"more-less\" ng-if=\"!question.toggle && (question.showMoreLess && question.hasExtra)\">    <button class=\"btn-default\" ng-click=\"question.toggleExtra($event)\">{{ question.showExtra === true ? settings.Language.Less : settings.Language.More }}</button></span>",
            link: function (scope, elem, attr) {

                /* This is a hacked way of getting to the celController scope so we can modify the same scope.loading object */
                var parent = scope;
                var parentId = scope.$id;
                while (parent.$parent && parent.$id != scope.mainScopeId) {
                    parent = parent.$parent;
                    parentId = parent.$id;
                }

                scope.question.showExtra = false;
                scope.question.hasExtra = scope.question.ExtraAnswers.length > 1 ? true : false;
                scope.question.showMoreLess = celConfig.Settings.Refinements.ShowMoreLess;
                scope.question.enabled = true;
                scope.question.showApply = false;

                /* Searchable Answers */
                scope.question.Search = { Text: "" };
                scope.question.AllAnswers = scope.question.Answers.concat(scope.question.ExtraAnswers);
                scope.question.AnswerLimit = celConfig.Settings.Refinements.ShowMoreLess === true ? celConfig.Settings.Refinements.AnswerLimit : scope.question.AllAnswers.length;
                scope.question.SearchableAnswers = celConfig.Settings.Refinements.SearchableAnswers;
                scope.question.SearchableAnswersThreshold = celConfig.Settings.Refinements.SearchableAnswersThreshold;
                /* /Searchable Answers */

                var visibilityList = celConfig.Settings.Refinements.RefinementVisibilityList;
                scope.question.toggle = celConfig.Settings.Refinements.RefinementVisibilityType === '0';
                if (visibilityList.indexOf(scope.question.SideText) > 0) {
                    scope.question.toggle = !scope.question.toggle;
                }
                elem.bind("click", function (event) {
                    //event.stopPropagation();
                    scope.$apply(function () {
                        scope.question.toggle = !scope.question.toggle;
                        if (scope.question.ID === 'PriceQuestion') {
                            parent.showSlider = !parent.showSlider;
                        }
                    });
                });

                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        //event.stopPropagation();
                        scope.$apply(function () {
                            scope.question.toggle = !scope.question.toggle;
                            if (scope.question.ID === 'PriceQuestion') {
                                parent.showSlider = !parent.showSlider;
                            }
                        });
                    }
                });

                var prevLimit = scope.question.AnswerLimit;
                elem.find("input").bind("keyup", function (event) {
                    var input = this;
                    scope.$apply(function () {
                        if (input.value.length > 0 && scope.question.showMoreLess) {
                            prevLimit = scope.question.AnswerLimit;
                            scope.question.AnswerLimit = scope.question.AllAnswers.length;
                            scope.question.showMoreLess = false;
                        } else if (input.value.length === 0) {
                            scope.question.AnswerLimit = prevLimit;
                            scope.question.showMoreLess = celConfig.Settings.Refinements.ShowMoreLess;
                        }
                    });
                });

                scope.question.toggleExtra = function (event) {
                    event.stopPropagation();
                    if (scope.question.AnswerLimit === scope.question.AllAnswers.length) {
                        scope.question.AnswerLimit = celConfig.Settings.Refinements.AnswerLimit;
                        scope.question.showExtra = false;
                    } else {
                        scope.question.AnswerLimit = scope.question.AllAnswers.length;
                        scope.question.showExtra = true;
                    }
                };
            }
        };
    }]);

angular
    .module("celebrosUI")
    .directive("celSearchAnlx", ['celAnlx', 'celConfig', function (celAnlx, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
                scope.$watch("results.Query", function (newQuery, oldQuery) {
                    if (newQuery === undefined)
                        return;

                    var cid = celConfig.Settings.General.AnalyticsCustomerName !== "" ? celConfig.Settings.General.AnalyticsCustomerName : celConfig.Settings.General.SiteKey;
                    var data = {
                        type: "SR",
                        responseType: "JSON",
                        cid: cid,
                        src: window.location.toString(),
                        ref: document.referrer,
                        ssid: scope.sessionId,
                        wsid: "",
                        lh: scope.results.LogHandle
                    };
                    celAnlx.sendAnlx(data);
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .factory("celSort", ["$location", "celConfig", function ($location, celConfig) {
        var CurrentSort = {
            Key: "",
            Alias: "",
            FieldName: "",
            Ascending: true,
            Numeric: false
        };

        var SortOptions = celConfig.Settings.Toolbar.SortOptions;

        var SetSort = function (key, asc) {
            CurrentSort.Key = key;
            CurrentSort.Alias = SortOptions[key].Alias;
            CurrentSort.FieldName = SortOptions[key].FieldName;
            CurrentSort.Ascending = asc !== undefined ? asc : SortOptions[key].Ascending;
            CurrentSort.Numeric = SortOptions[key].Numeric;
        };

        var SetSortByAlias = function (alias, asc) {
            for (option in SortOptions) {
                if (SortOptions[option].Alias === alias) {
                    SetSort(option, asc);
                }
            }
        };

        var SetSortByFieldName = function (fieldName, asc) {
            for (option in SortOptions) {
                if (SortOptions[option].FieldName === fieldName) {
                    if (!SortOptions[option].SingleOrder) {
                        SetSort(option, asc);
                    } else if (SortOptions[option].Ascending === asc) {
                        SetSort(option, asc);
                    }
                }
            }
        };

        var GetCurrentSort = function () {
            return {
                alias: CurrentSort.Alias,
                fieldName: CurrentSort.FieldName,
                isAscending: CurrentSort.Ascending,
                isNumericSort: CurrentSort.Numeric
            };
        };

        for (option in SortOptions) {
            SortOptions[option].Ascending = SortOptions[option].Ascending !== undefined ? toBoolean(SortOptions[option].Ascending) : true; /* Defaults to true */
            SortOptions[option].Numeric = SortOptions[option].Numeric !== undefined ? toBoolean(SortOptions[option].Numeric) : false; /* Defaults to false */

            if (SortOptions[option].Alias === undefined || SortOptions[option].Alias === "") {
                SortOptions[option].Alias = option;
            }

            if (SortOptions[option].FieldName === undefined || SortOptions[option].FieldName === "") {
                SortOptions[option].FieldName = option;
            }

            if (SortOptions[option].Default !== undefined) {
                SortOptions[option].Default = toBoolean(SortOptions[option].Default);
                SetSort(option);
            }
        }

        if ($location.search()["SortBy"] !== undefined) {
            var alias = $location.search()["SortBy"];
            var ascending = $location.search()["Ascending"];

            for (option in SortOptions) {
                if (SortOptions[option].Alias === alias) {
                    SetSort(option, ascending);
                    break;
                }
            }
        }

        return {
            GetCurrentSort: GetCurrentSort,
            //SortOptions: SortOptions,
            SetSort: SetSort,
            SetSortByAlias: SetSortByAlias,
            SetSortByFieldName: SetSortByFieldName
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celSortOption", ["$location", "celAPI", "celConfig", "celSort", function ($location, celAPI, celConfig, celSort) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            template: "<span>{{ settings.Language[name] !== undefined ? settings.Language[name] : sort.Alias }}<i ng-hide=\"sort.Alias == 'Relevancy' || sort.Alias == 'Highest Rated' || sort.Alias != currentSort.alias\" ng-class=\"sortCSS\"></i></span>",
            link: function (scope, elem, attr) {

                /* This is a hacked way of getting to the celController scope */
                var parent = scope;
                var parentId = scope.$id;
                while (parent.$parent && parent.$id != scope.mainScopeId) {
                    parent = parent.$parent;
                    parentId = parent.$id;
                }

                if (scope.sort.Alias === undefined || scope.sort.alias === null) {
                    scope.sort.Alias = scope.name;
                }

                var getClass = function () {
                    var css = "";
                    if (parent.currentSort.alias === scope.sort.Alias) {
                        css = "cel-icon-sort-";
                        if (parent.currentSort.isNumericSort) {
                            css += "number-";
                        } else {
                            css += "name-";
                        }
                        if (parent.currentSort.isAscending) {
                            css += "up";
                        } else {
                            css += "down";
                        }
                    } else {
                        css = "";
                    }
                    return css;
                };

                var changeSort = function (sort, ascending, fieldName) {
                    celSort.SetSortByFieldName(fieldName, ascending);

                    parent.data = angular.merge(parent.data, celSort.GetCurrentSort());

                    switch (sort) {
                        case "Price":
                            celAPI.doSortByPrice(parent.data).then(scope.onResults, scope.onError);
                            break;
                        case "Relevancy":
                            celAPI.doSortByRank(parent.data).then(scope.onResults, scope.onError);
                            break;
                        default:
                            celAPI.doSortByField(parent.data).then(scope.onResults, scope.onError);
                            break;
                    }

                    parent.currentSort = angular.copy(celSort.GetCurrentSort());


                    if (sort === "Relevancy") {
                        $location.search("SortBy", null);
                        $location.search("Ascending", null);
                    } else {
                        $location.search("SortBy", sort);
                        $location.search("Ascending", ascending.toString());
                    }

                    if ($location.search()["Page"] !== undefined) {
                        $location.search("Page", null);
                    }

                    history.pushState(parent.data, null, $location.absUrl());
                };

                /* scope.sortCSS is used as the class for the sort elements. Only the active one will display. */
                scope.sortCSS = getClass();

                var selectSort = function () {
                    var isSingleOrder = scope.sort.SingleOrder !== undefined ? scope.sort.SingleOrder : false;
                    if (scope.currentSort.alias === scope.sort.Alias && isSingleOrder) {
                        /* Do nothing */
                    } else {
                        var isAscending = true;
                        var isNumeric = scope.sort.Numeric !== undefined ? scope.sort.Numeric : false;
                        if (scope.currentSort.alias === scope.sort.Alias) {
                            isAscending = !scope.currentSort.isAscending;
                        } else {
                            isAscending = scope.sort.Ascending !== undefined ? scope.sort.Ascending : true;
                        }

                        changeSort(scope.sort.Alias, isAscending, scope.sort.FieldName);
                    }
                    scope.$apply(function () {
                        scope.sortCSS = getClass();
                    });
                };

                elem.bind("click", function () {
                    selectSort();
                });

                elem.bind("keyup", function (event) {
                    if (event.which === 13) {
                        selectSort();
                    }
                });

                /* Back/Forward Handling
                 * Ensure that the correct CSS style is being used for the current sort
                 */
                scope.$on('$locationChangeSuccess', function (a, newUrl, oldUrl) {
                    scope.sortCSS = getClass();
                });
            }
        };
    }]);
angular
    .module("celebrosUI")
    .directive("celToolbar", function () {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            templateUrl: "partials/toolbar.html",
            link: function (scope, elem, attr) {
            }
        };
    });
/* Use this file to add custom directives that a client may need/want
 * For reference, take a look at the celHelpers.js file. That file contains some small helper directives.
 *
 * You can look up how directives work at the link below, or you can ask me - Nick
 * https://docs.angularjs.org/guide/directive
 * */

 angular
    .module("celebrosUI")
    .directive("celSort", ['$location', 'celAPI', 'celConfig', function ($location, celAPI, celConfig) {
        return {
            restrict: "A", //E = element, A = attribute, C = class, M = comment
            link: function (scope, elem, attr) {
            	/* This is a hacked way of getting to the celController scope */
                var parent = scope;
                var parentId = scope.$id;
                while (parent.$parent && parent.$id != scope.mainScopeId) {
                    parent = parent.$parent;
                    parentId = parent.$id;
                }

                var changeSort = function (sort, alias, ascending, numeric) {
                    scope.data.fieldName = celConfig.Settings.Toolbar.SortOptions[sort].FieldName !== undefined ? celConfig.Settings.Toolbar.SortOptions[sort].FieldName : sort;
                    scope.data.isAscending = ascending;
                    scope.data.isNumericSort = numeric;
                    //scope.data.isAscending = scope.data.isAscending == 1 ? 0 : 1;
                    //var data = JSON.parse(JSON.stringify(scope.data));

                    switch (scope.data.fieldName) {
                        case "Price":
                            celAPI.doSortByPrice(scope.data).then(scope.onResults, scope.onError);
                            break;
                        case "Relevancy":
                            celAPI.doSortByRank(scope.data).then(scope.onResults, scope.onError);
                            break;
                        //case "Title":
                        //case "Name":
                        //celAPI.doSortByName(data).then(onResults, onError);
                        //break;
                        default:
                            celAPI.doSortByField(scope.data).then(scope.onResults, scope.onError);
                            break;
                    }

                    scope.currentSort.alias = alias;
                    scope.currentSort.fieldName = scope.data.fieldName;
                    scope.currentSort.isAscending = toBoolean(scope.data.isAscending);
                    scope.currentSort.isNumericSort = toBoolean(scope.data.isNumericSort);


                    if (sort === "Relevancy") {
                        $location.search("SortBy", null);
                        $location.search("Ascending", null);
                    } else {
                        $location.search("SortBy", scope.data.fieldName);
                        $location.search("Ascending", ascending.toString());
                    }

                    if ($location.search()["Page"] !== undefined) {
                        $location.search("Page", null);
                    }

                    history.pushState(scope.data, null, $location.absUrl());
                };

                elem.bind("change", function (event) {
                    var value = event.target.value;
                    if (scope.currentSort.alias === value) {
                        return;
                    }
                    var sortKey = "";
		            for (key in celConfig.Settings.Toolbar.SortOptions) {
						if(celConfig.Settings.Toolbar.SortOptions[key].Alias === value) {
					        sortKey = key;
					    }
					}
					if (value === "Relevancy") {
						sortKey = value;
					}

					var sort = celConfig.Settings.Toolbar.SortOptions[sortKey];
                    var isAscending = true;
                    var isNumeric = sort.Numeric !== undefined ? sort.Numeric : false;
                    if (scope.currentSort.alias === sort.Alias) {
                        isAscending = !scope.currentSort.isAscending;
                    } else {
                        isAscending = sort.Ascending !== undefined ? sort.Ascending : true;
                    }

                    changeSort(sortKey, sort.Alias, isAscending, isNumeric);
                });
            }
        };
    }]);


/* Do not remove, this is needed for AngularJS HTML5 mode to work */
/* First we need to sanitize the URL so there is no script/HTML injection */
/* Sanitize URL */
var tagBody = '(?:[^"\'>]|"[^"]*"|\'[^\']*\')*';

var tagOrComment = new RegExp(
    '<(?:'
    // Comment body.
    + '!--(?:(?:-*[^->])*--+|-?)'
    // Special "raw text" elements whose content should be elided.
    + '|script\\b' + tagBody + '>[\\s\\S]*?</script\\s*'
    + '|style\\b' + tagBody + '>[\\s\\S]*?</style\\s*'
    // Regular name
    + '|/?[a-z]'
    + tagBody
    + ')>',
    'gi');
function stripHtml(html) {
    var oldHtml;
    do {
        oldHtml = html;
        html = html.replace(tagOrComment, '');
    } while (html !== oldHtml);
    return html.replace(/</g, '&lt;');
}

/* Then we need ot add the base URL so that AngularJS HTML5 works */
var elem = document.createElement("base");
elem.href = document.location;
document.head.appendChild(elem);

/* We need to dynamically load the CSS
 * This should probably be done in a more 'Angular' way... but this is how it's done for now
 */
function LoadCSS(css) {
    var path, file;
    if (css === undefined || css === null) {
        var scripts = document.getElementsByTagName('script');
        var celJS = "";
        var isMin = false;
        for (i = 0; i < scripts.length; i++) {
            if (scripts[i].src.toLowerCase().indexOf('celscripts.') > 0) {
                if (scripts[i].src.toLowerCase().indexOf('.min') > 0) {
                    isMin = true;
                }
                celJS = scripts[i];
                break;
            }
        }

        path = celJS.src.toString().replace(/CelScripts[.min]*.js/ig, "");
        file = "CelStyles.min.css";
        if (!isMin) {
            file = "../css/CelStyles.css";
        }
    } else {
        path = "";
        file = css;
    }
    elem = document.createElement("link");
    elem.href = path + file;
    elem.rel = "stylesheet";
    elem.type = "text/css";
    document.head.appendChild(elem);
}

/*
 *  Helper Functions
 */

/* Escapes HTML */
function escapeHtml(text) {
    var map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };

    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}

/**
 * isNumeric
 * Checks if the object sent is numeric
 * @param {any} n the variable to check
 * @returns {boolean} if the parameter is a number
 */
function isNumeric(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

/**
 * isBoolean
 * Checks if the object sent is a boolean value
 * @param {any} n the variable to check
 *  @returns {boolean} if the parameter is a boolean
 */
function isBoolean(n) {
    return typeof n === "boolean";
}

function toBoolean(val) {
    if (isBoolean(val)) {
        return val;
    } else if (isNumeric(val)) {
        return Boolean(Number(val));
    } else {
        return val === "true";
    }
}

/* Polyfill URLSearchParams*/
(function (w) {

    w.URLSearchParams = w.URLSearchParams || function (searchString) {
        var self = this;
        self.searchString = searchString;
        self.get = function (name) {
            var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(self.searchString);
            if (results == null) {
                return null;
            }
            else {
                return decodeURI(results[1]) || 0;
            }
        };
    }

})(window)
function instantSearch(searchId, charThreshold) {
    var inputs = [];
    if (charThreshold < 0) {
        charThreshold = 0;
    }
    var addListener = function () {
        if (inputs.length > 0) {
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                input.addEventListener('keyup', function (event) {
                    var appElement = document.getElementById('celUITDiv');
                    var $scope = angular.element(appElement).injector().get("$rootScope");
                    $scope = $scope.$$childHead;
                    $scope.$apply(function () {
                        if (event.currentTarget.value.length >= charThreshold || event.currentTarget.value.length === 0) {
                            $scope.searchQuery = event.currentTarget.value;
                        }
                    });
                });

                input.addEventListener('keydown', function (event) {
                    if (event.keyCode === 13) {
                        event.preventDefault();
                        return;
                    }
                });

                //Ensure that the parent element doesn't reload the page when onsubmit is called
                if (input.parentElement.nodeName === "FORM") {
                    input.parentElement.onsubmit = function (event) {
                        event.preventDefault();
                        return;
                    };
                }
            }
        }
    };
    var search = document.getElementById(searchId);
    if (search !== null) {
        inputs.push(search);
    } else {
        inputs = document.getElementsByName(searchId);
    }
    if (inputs.length > 0) {
        addListener();
    } else {
        document.addEventListener("DOMContentLoaded", function (event) {
            addListener();
        });
    }
}
angular.element(document).ready(function() {
	var urlParams = new URLSearchParams(window.location.search);
	try {
		angular.bootstrap(document.getElementById('celUITDiv'), ['celebrosUI']);
		if(urlParams.get("debug") !== null) {
			console.log("celebrosUI loaded");
		}
	}  catch (e) {
		if(urlParams.get("debug") !== null) {
            console.log("celbrosUI not ready, retrying in 200ms");
			console.log(e);
		}
		/* If for some reason it doesn't load, retry with a 200ms delay */
		setTimeout(function(){
			angular.bootstrap(document.getElementById('celUITDiv'), ['celebrosUI']);
		}, 200);
	}
});
}());
