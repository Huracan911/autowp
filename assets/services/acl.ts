import * as angular from "angular";
import Module from 'app.module';

const SERVICE_NAME = 'AclService';

type StringBooleanMap = Map<string, boolean>;

export class AclService {
    static $inject = ['$q', '$http'];
    private isAllowedCache: Map<string, StringBooleanMap> = new Map<string, StringBooleanMap>();
    private cache: StringBooleanMap = new Map<string, boolean>();
  
    constructor(
        private $q: ng.IQService,
        private $http: ng.IHttpService
    ){}
  
    public inheritsRole = function(role: string, rejectError: any): ng.IPromise<boolean> {
        var self = this;
        return this.$q(function(resolve: ng.IQResolveReject<boolean>, reject: ng.IQResolveReject<any>) {
          
            if (self.cache.has(role)) {
                if (self.cache.get(role)) {
                    resolve(true);
                } else {
                    reject(rejectError);
                }
                return;
            }
          
            self.$http({
                method: 'GET',
                url: '/api/acl/inherit-roles',
                params: {
                    roles: role
                }
            }).then(function(response: ng.IHttpResponse<any>) {
                let value = response.data[role];
                self.cache.set(role, value);
                if (value) {
                    resolve(true);
                } else {
                    reject(rejectError);
                }
            }, function() {
                self.cache.set(role, false);
                reject(rejectError);
            });
        });
    };
    
    public isAllowed = function(resource: string, privilege: string, rejectError: any): ng.IPromise<boolean> {
        var self = this;
        return this.$q(function(resolve: ng.IQResolveReject<boolean>, reject: ng.IQResolveReject<any>) {
            
            var hasCache = self.isAllowedCache.has(resource) && self.isAllowedCache.get(resource).has(privilege);
          
            if (hasCache) {
                if (self.isAllowedCache.get(resource).get(privilege)) {
                    resolve(true);
                } else {
                    reject(rejectError);
                }
                return;
            }
          
            if (! self.isAllowedCache.has(resource)) {
                self.isAllowedCache.set(resource, new Map<string, boolean>());
            }
            
            self.$http({
                method: 'GET',
                url: '/api/acl/is-allowed',
                params: {
                    resource: resource,
                    privilege: privilege
                }
            }).then(function(response: ng.IHttpResponse<any>) {
                self.isAllowedCache.get(resource).set(privilege, response.data.result);
                if (response.data.result) {
                    resolve(true);
                } else {
                    reject(rejectError);
                }
            }, function() {
                self.isAllowedCache.get(resource).set(privilege, false);
                resolve(false);
            });
           
        });
    };
    
};

angular.module(Module).service(SERVICE_NAME, AclService);

