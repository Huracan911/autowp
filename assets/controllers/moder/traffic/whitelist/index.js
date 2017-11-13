import angular from 'angular';
import Module from 'app.module';
import template from './template.html';
import { IpService } from 'services/ip';
import { AclService } from 'services/acl';

const CONTROLLER_NAME = 'ModerTrafficWhitelistController';
const STATE_NAME = 'moder-traffic-whitelist';

angular.module(Module)
    .config(['$stateProvider',
        function config($stateProvider) {
            $stateProvider.state( {
                name: STATE_NAME,
                url: '/moder/traffic/whitelist',
                controller: CONTROLLER_NAME,
                controllerAs: 'ctrl',
                template: template,
                resolve: {
                    access: ['AclService', function (Acl) {
                        return Acl.inheritsRole('moder', 'unauthorized');
                    }]
                }
            });
        }
    ])
    .controller(CONTROLLER_NAME, [
        '$scope', '$http', 'IpService',
        function($scope, $http, IpService) {
            $scope.pageEnv({
                layout: {
                    isAdminPage: true,
                    blankPage: false,
                    needRight: false
                },
                name: 'page/77/name',
                pageId: 77
            });
            
            $http({
                method: 'GET',
                url: '/api/traffic/whitelist'
            }).then(function(response) {
                $scope.items = response.data.items;
                
                /*angular.forEach($scope.items, function(item) {
                    IpService.getHostByAddr(item.ip).then(function(hostname) {
                        item.hostname = hostname;
                    });
                });*/
            }, function() {
                $state.go('error-404');
            });
            
            $scope.deleteItem = function(item) {
                $http({
                    method: 'DELETE',
                    url: '/api/traffic/whitelist/' + item.ip
                }).then(function(response) {
                    var index = $scope.items.indexOf(item);
                    if (index !== -1) {
                        $scope.items.splice(index, 1);
                    }
                    /*angular.forEach($scope.items, function(item) {
                        IpService.getHostByAddr(item.ip).then(function(hostname) {
                            item.hostname = hostname;
                        });
                    });*/
                });
            };
        }
    ]);

export default CONTROLLER_NAME;
